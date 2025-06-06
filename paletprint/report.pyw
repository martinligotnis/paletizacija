import threading
import asyncio
import logging
from datetime import datetime, timedelta, time
from telethon.sync import TelegramClient
import mysql.connector

# === LOGGING CONFIGURATION ===
LOG_FILE = r"C:\logs\startup_report_log.txt"
logging.basicConfig(
    filename=LOG_FILE,
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    encoding="utf-8"
)
logging.info("=== Script started ===")

# --- TELEGRAM CONFIG ---
api_id       = 27858609
api_hash     = '3e5f4ee759dd212072532b9db5793abd'
session_name = r"C:\xampp\htdocs\paletprint\rita_zinojums.session"
group_link   = 'https://t.me/+n02K7B1y12E0MmVk'

# --- REPORT SCHEDULE ---
REPORT_INTERVAL = 300.0   # seconds

def compose_8am_message(day_start, overnight_summary, produced_overnight,
                        today_summary, producing_today, overall_efficiency):
    parts = []
    if produced_overnight:
        parts.append(f"Overnight ({(day_start - timedelta(days=1)).date()}):")
        for row in overnight_summary:
            parts.append(f" - {row['ProduktaNosaukums']}: {row['pallets']} pallets")
        parts.append(f"Overall efficiency: {overall_efficiency*100:.2f}%\n")
    if producing_today:
        first = today_summary[0]
        parts.append(
          f"{day_start.date()} starting with {first['ProduktaNosaukums']} "
          f"({first['first_pallet']:%H:%M})"
        )
        parts.append(f"Today's efficiency: {overall_efficiency*100:.2f}%")
    else:
        parts.append("No production registered today yet.")
    return "\n".join(parts)

def compose_midday_message(day_start, today_summary, overall_efficiency, hour):
    lines = [f"{day_start.date()} status at {hour:02d}:00"]
    for row in today_summary:
        lines.append(f" - {row['ProduktaNosaukums']} {row['Tilpums']}L: {row['pallets']} pallets")
    lines.append(f"Efficiency so far: {overall_efficiency*100:.2f}%")
    return "\n".join(lines)

def compose_final_message(day_start, today_summary, overall_efficiency):
    lines = [f"Final report for {day_start.date()}:"]
    for row in today_summary:
        lines.append(f" - {row['ProduktaNosaukums']}: {row['pallets']} pallets")
    lines.append(f"Overall efficiency: {overall_efficiency*100:.2f}%")
    return "\n".join(lines)

def send_report():
    # schedule next run
    threading.Timer(REPORT_INTERVAL, send_report).start()

    now = datetime.now()
    current_time = now.time()
    logging.info("Checking report at %s", now.strftime("%Y-%m-%d %H:%M:%S"))

    # compute day_start at 6 AM (or previous day if before 6)
    day_start = datetime(now.year, now.month, now.day, 6, 0)
    if current_time < time(6,0):
        day_start -= timedelta(days=1)

    # --- DB QUERIES ---
    try:
        conn = mysql.connector.connect(
            host="127.0.0.1", user="venden",
            password="venden_SQl_2024", database="venden"
        )
        cursor = conn.cursor(dictionary=True)
    except Exception:
        logging.exception("DB connection failed")
        return

    # overnight window: 00:30–06:00
    y0030 = datetime.combine(day_start.date(), time(0,30))
    overnight_start = y0030 if y0030 < day_start else (y0030 - timedelta(days=1))

    cursor.execute(
        "SELECT COUNT(*) cnt FROM paletes "
        "WHERE DatumsLaiks >= %s AND DatumsLaiks < %s",
        (overnight_start, day_start)
    )
    produced_overnight = cursor.fetchone()['cnt'] > 0

    overnight_summary = []
    if produced_overnight:
        cursor.execute(
            "SELECT p.ProduktaNr, pr.ProduktaNosaukums, pr.Tilpums, COUNT(*) AS pallets "
            "FROM paletes p "
            "LEFT JOIN produkti pr ON pr.ProduktaNr=p.ProduktaNr "
            "WHERE p.DatumsLaiks >= %s AND p.DatumsLaiks < %s "
            "GROUP BY p.ProduktaNr",
            (overnight_start, day_start)
        )
        overnight_summary = cursor.fetchall()

    # today’s summary
    cursor.execute(
        "SELECT p.ProduktaNr, pr.ProduktaNosaukums, pr.Tilpums, COUNT(*) AS pallets, "
        "MIN(p.DatumsLaiks) AS first_pallet "
        "FROM paletes p "
        "LEFT JOIN produkti pr ON pr.ProduktaNr=p.ProduktaNr "
        "WHERE p.DatumsLaiks >= %s AND p.DatumsLaiks < %s "
        "GROUP BY p.ProduktaNr ORDER BY first_pallet",
        (day_start, now)
    )
    today_summary = cursor.fetchall()
    producing_today = bool(today_summary)

    # efficiency calculation
    cursor.execute("SELECT ProduktaNr, ProduktiPalete, LinijasAtrums FROM produkti")
    prod_info = {r['ProduktaNr']: r for r in cursor.fetchall()}

    cursor.execute(
        "SELECT * FROM paletes WHERE DatumsLaiks >= %s AND DatumsLaiks < %s",
        (day_start, now)
    )
    pallets_today = cursor.fetchall()

    cumulative = 0
    if pallets_today:
        for p in pallets_today:
            info = prod_info.get(p['ProduktaNr'], {})
            cumulative += info.get('ProduktiPalete', 1)
        elapsed_h = (now - day_start).total_seconds() / 3600
        max_speed = max(
            (prod_info[r['ProduktaNr']]['LinijasAtrums'] for r in today_summary),
            default=1
        )
        nominal = max_speed * elapsed_h if elapsed_h > 0 else 0
        efficiency = cumulative / nominal if nominal else 0
    else:
        efficiency = 0

    conn.close()

    # --- DECIDE WHICH REPORT TO SEND ---
    message = None

    # 8 AM morning report
    if time(8,0) <= current_time < time(8,5):
        message = compose_8am_message(
            day_start, overnight_summary, produced_overnight,
            today_summary, producing_today, efficiency
        )

    # Hourly midday reports from 9 AM through 8 PM
    elif any(time(h,0) <= current_time < time(h,5) for h in range(9, 21)):
        for h in range(9, 21):
            if time(h,0) <= current_time < time(h,5):
                message = compose_midday_message(day_start, today_summary, efficiency, h)
                break

    # Final report at 12:30 AM
    elif time(0,30) <= current_time < time(0,35):
        message = compose_final_message(day_start, today_summary, efficiency)

    if not message:
        logging.info("No report to send at this time.")
        return

    # --- SEND VIA TELETHON (per-thread loop) ---
    try:
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        client = TelegramClient(session_name, api_id, api_hash, loop=loop)
        client.start()
        chat = client.get_entity(group_link)
        client.send_message(chat, message)
        client.disconnect()
        logging.info("Sent report:\n%s", message)
    except Exception:
        logging.exception("Failed to send Telegram report")


# Kick off the loop immediately
send_report()