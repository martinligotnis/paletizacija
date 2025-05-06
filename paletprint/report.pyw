import threading
from telethon.sync import TelegramClient
import mysql.connector
from datetime import datetime, timedelta, time

# --- CONFIGURATION ---
api_id       = 27858609
api_hash     = '3e5f4ee759dd212072532b9db5793abd'
session_name = 'rita_zinojums'
group_link   = 'https://t.me/+n02K7B1y12E0MmVk'

# How often (in seconds) to wake up and check for a report
REPORT_INTERVAL = 300.0

# --- SCHEDULED TIMES ---
report_times = [
    (8, 0), (12, 0), (15, 0), (18, 0), (0, 30)
]

def get_next_report_time(now):
    """Not used in this pattern, but you can compute next report if needed."""
    today = now.date()
    for h, m in report_times:
        t = time(h, m)
        if now.time() < t:
            return datetime.combine(today, t)
    return datetime.combine(today + timedelta(days=1), time(*report_times[0]))

def compose_8am_message(day_start, overnight_summary, produced_overnight,
                        today_summary, producing_today, overall_efficiency):
    msg = []
    if produced_overnight:
        msg.append(f"Overnight production update for {(day_start - timedelta(days=1)).date()}:\n")
        for prod in overnight_summary:
            msg.append(f"- {prod['ProduktaNosaukums']} produced {prod['pallets']} pallets")
        msg.append(f"Overall efficiency: {overall_efficiency*100:.2f}%\n")
    if producing_today and today_summary:
        first_prod = today_summary[0]
        msg.append(
            f"{day_start.date()} we start with {first_prod['ProduktaNosaukums']} "
            f"and first pallet at {first_prod['first_pallet']}."
        )
        msg.append(f"Today's efficiency: {overall_efficiency*100:.2f}%")
    else:
        msg.append("No production registered today yet.")
    return '\n'.join(msg)

def compose_midday_message(day_start, today_summary, overall_efficiency, report_hour):
    lines = [f"{day_start.date()} status at {report_hour:02d}:00:"]
    for prod in today_summary:
        lines.append(f"- {prod['ProduktaNosaukums']}: {prod['pallets']} pallets")
    lines.append(f"Efficiency so far: {overall_efficiency*100:.2f}%")
    return '\n'.join(lines)

def send_report():
    # Schedule next run
    threading.Timer(REPORT_INTERVAL, send_report).start()

    now = datetime.now()
    today = now.date()
    current_time = now.time()

    # Compute production window
    day_start = datetime(now.year, now.month, now.day, 6, 0)
    if current_time < time(6, 0):
        day_start -= timedelta(days=1)

    # Connect to DB
    conn = mysql.connector.connect(
        host="127.0.0.1",
        user="venden",
        password="venden_SQl_2024",
        database="venden"
    )
    cursor = conn.cursor(dictionary=True)

    # Overnight window (00:30–06:00)
    y0030 = datetime.combine(day_start.date(), time(0, 30))
    overnight_start = y0030 if y0030 < day_start else (y0030 - timedelta(days=1))
    cursor.execute(
        "SELECT COUNT(*) AS cnt FROM paletes "
        "WHERE DatumsLaiks >= %s AND DatumsLaiks < %s",
        (overnight_start, day_start)
    )
    produced_overnight = cursor.fetchone()['cnt'] > 0

    overnight_summary = []
    if produced_overnight:
        cursor.execute(
            "SELECT p.ProduktaNr, pr.ProduktaNosaukums, COUNT(*) AS pallets "
            "FROM paletes p "
            "LEFT JOIN produkti pr ON pr.ProduktaNr = p.ProduktaNr "
            "WHERE p.DatumsLaiks >= %s AND p.DatumsLaiks < %s "
            "GROUP BY p.ProduktaNr",
            (overnight_start, day_start)
        )
        overnight_summary = cursor.fetchall()

    # Today’s summary
    cursor.execute(
        "SELECT p.ProduktaNr, pr.ProduktaNosaukums, COUNT(*) AS pallets, "
        "MIN(p.DatumsLaiks) AS first_pallet "
        "FROM paletes p "
        "LEFT JOIN produkti pr ON pr.ProduktaNr = p.ProduktaNr "
        "WHERE p.DatumsLaiks >= %s AND p.DatumsLaiks < %s "
        "GROUP BY p.ProduktaNr "
        "ORDER BY first_pallet",
        (day_start, now)
    )
    today_summary = cursor.fetchall()
    producing_today = bool(today_summary)

    # Efficiency
    cursor.execute("SELECT ProduktaNr, ProduktiPalete, LinijasAtrums FROM produkti")
    product_info = {r['ProduktaNr']: r for r in cursor.fetchall()}

    cursor.execute(
        "SELECT * FROM paletes WHERE DatumsLaiks >= %s AND DatumsLaiks < %s",
        (day_start, now)
    )
    pallets_today = cursor.fetchall()

    cumulative_units = 0
    if pallets_today:
        for p in pallets_today:
            info = product_info.get(p['ProduktaNr'], {})
            cumulative_units += info.get('ProduktiPalete', 1)
        elapsed_h = (now - day_start).total_seconds() / 3600
        max_speed = max(
            (product_info[r['ProduktaNr']]['LinijasAtrums'] for r in today_summary),
            default=1
        )
        nominal = max_speed * elapsed_h if elapsed_h > 0 else 0
        overall_efficiency = (cumulative_units / nominal) if nominal else 0
    else:
        overall_efficiency = 0

    conn.close()

    # Build message if in a 5-minute window around your report times
    message = None
    if time(8,0) <= current_time < time(8,5):
        message = compose_8am_message(
            day_start, overnight_summary, produced_overnight,
            today_summary, producing_today, overall_efficiency
        )
    elif time(12,0) <= current_time < time(12,10):
        message = compose_midday_message(day_start, today_summary,
                                         overall_efficiency, 12)
    elif time(15,0) <= current_time < time(15,10):
        message = compose_midday_message(day_start, today_summary,
                                         overall_efficiency, 15)
    elif time(18,0) <= current_time < time(18,10):
        message = compose_midday_message(day_start, today_summary,
                                         overall_efficiency, 18)
    elif time(0,30) <= current_time < time(0,40):
        message = compose_midday_message(day_start, today_summary,
                                         overall_efficiency, 0)

    if message:
        client = TelegramClient(session_name, api_id, api_hash)
        client.start()
        chat = client.get_entity(group_link)
        client.send_message(chat, message)
        client.disconnect()
        print(f"[{now}] Sent report:\n{message}")
    else:
        print(f"[{now}] No report needed at this time.")


# Kick off the 5-minute loop immediately
send_report()








# from telethon.sync import TelegramClient
# import mysql.connector

# # 1) Fetch rows
# conn = mysql.connector.connect(
#     host="127.0.0.1",
#     user="root",
#     password="",
#     database="venden"
# )
# cursor = conn.cursor()
# cursor.execute(
#     "SELECT Apraksts FROM venden.paletes ORDER BY DatumsLaiks DESC LIMIT 2"
# )
# rows = cursor.fetchall()

# # 2) Start client
# api_id   = 27858609
# api_hash = '3e5f4ee759dd212072532b9db5793abd'
# client   = TelegramClient('rita_zinojums', api_id, api_hash)
# client.start()

# # 3) Get the group entity by passing the full link
# link  = 'https://t.me/+n02K7B1y12E0MmVk'
# group = client.get_entity(link)

# # 4) Send your messages
# for (text,) in rows:
#     client.send_message(group, text)
#     print("Sent:", text)

# client.disconnect()