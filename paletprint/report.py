from telethon.sync import TelegramClient
import mysql.connector
from datetime import datetime, timedelta, time

# --- CONFIGURATION ---
api_id   = 27858609
api_hash = '3e5f4ee759dd212072532b9db5793abd'
session_name = 'rita_zinojums'
group_link  = 'https://t.me/+n02K7B1y12E0MmVk'

# --- SCHEDULED TIMES ---
report_times = [
    (8, 0), (12, 0), (15, 0), (18, 0), (0, 30)
]

# --- DATABASE CONNECTION ---
conn = mysql.connector.connect(
    host="127.0.0.1",
    user="venden",
    password="venden_SQl_2024",
    database="venden"
)
cursor = conn.cursor(dictionary=True)

# --- TIME LOGIC ---
now = datetime.now()
today = now.date()
current_time = now.time()

# Find the next scheduled report time
def get_next_report_time():
    for h, m in report_times:
        t = time(h, m)
        if current_time < t:
            return datetime.combine(today, t)
    # If none left today, next is the first tomorrow
    return datetime.combine(today + timedelta(days=1), time(*report_times[0]))

# --- PRODUCTION DAY WINDOW ---
day_start = datetime(now.year, now.month, now.day, 6, 0)
if now.time() < time(6,0):
    day_start -= timedelta(days=1)
day_end = day_start + timedelta(days=1)

# --- OVERNIGHT PRODUCTION (for 8:00 message) ---
yesterday = day_start - timedelta(days=1)
yesterday_0030 = datetime.combine(day_start.date(), time(0,30))
if yesterday_0030 < day_start:
    overnight_start = yesterday_0030
else:
    overnight_start = yesterday_0030 - timedelta(days=1)

cursor.execute(
    """
    SELECT COUNT(*) as cnt FROM paletes WHERE DatumsLaiks >= %s AND DatumsLaiks < %s
    """,
    (overnight_start.strftime('%Y-%m-%d %H:%M:%S'), day_start.strftime('%Y-%m-%d %H:%M:%S'))
)
produced_overnight = cursor.fetchone()['cnt'] > 0

overnight_summary = []
if produced_overnight:
    cursor.execute(
        """
        SELECT p.ProduktaNr, pr.ProduktaNosaukums, COUNT(*) as pallets
        FROM paletes p
        LEFT JOIN produkti pr ON pr.ProduktaNr = p.ProduktaNr
        WHERE p.DatumsLaiks >= %s AND p.DatumsLaiks < %s
        GROUP BY p.ProduktaNr
        """,
        (overnight_start.strftime('%Y-%m-%d %H:%M:%S'), day_start.strftime('%Y-%m-%d %H:%M:%S'))
    )
    overnight_summary = cursor.fetchall()

# --- TODAY'S PRODUCTION SUMMARY ---
cursor.execute(
    """
    SELECT p.ProduktaNr, pr.ProduktaNosaukums, COUNT(*) as pallets, MIN(p.DatumsLaiks) as first_pallet
    FROM paletes p
    LEFT JOIN produkti pr ON pr.ProduktaNr = p.ProduktaNr
    WHERE p.DatumsLaiks >= %s AND p.DatumsLaiks < %s
    GROUP BY p.ProduktaNr
    ORDER BY first_pallet ASC
    """,
    (day_start.strftime('%Y-%m-%d %H:%M:%S'), now.strftime('%Y-%m-%d %H:%M:%S'))
)
today_summary = cursor.fetchall()

producing_today = len(today_summary) > 0

# --- EFFICIENCY CALCULATION (same as statistics page) ---
cursor.execute("SELECT ProduktaNr, ProduktiPalete, LinijasAtrums FROM produkti")
product_info = {row['ProduktaNr']: row for row in cursor.fetchall()}

cursor.execute(
    "SELECT * FROM paletes WHERE DatumsLaiks >= %s AND DatumsLaiks < %s ORDER BY DatumsLaiks ASC",
    (day_start.strftime('%Y-%m-%d %H:%M:%S'), now.strftime('%Y-%m-%d %H:%M:%S'))
)
pallets_today = cursor.fetchall()

cumulative_units = 0
cumulative_nominal = 0
if pallets_today:
    for p in pallets_today:
        prod = product_info.get(p['ProduktaNr'])
        units_per_pallet = prod['ProduktiPalete'] if prod else 1
        line_speed = prod['LinijasAtrums'] if prod else 1
        cumulative_units += units_per_pallet
    elapsed_hours = (now - day_start).total_seconds() / 3600
    if today_summary:
        max_speed = max(product_info.get(row['ProduktaNr'], {}).get('LinijasAtrums', 1) for row in today_summary)
    else:
        max_speed = 1
    cumulative_nominal = max_speed * elapsed_hours
    overall_efficiency = (cumulative_units / cumulative_nominal) if cumulative_nominal > 0 else None
else:
    overall_efficiency = None

conn.close()

# --- MESSAGE COMPOSERS ---
def compose_8am_message():
    msg = []
    if produced_overnight:
        msg.append(f"Overnight production update for {(day_start - timedelta(days=1)).date()}:\n")
        for prod in overnight_summary:
            msg.append(f"- {prod['ProduktaNosaukums']} produced {prod['pallets']} pallets")
        msg.append(f"Overall efficiency: {overall_efficiency*100:.2f}%\n")

    if producing_today and today_summary:
        first_prod = today_summary[0]
        msg.append(f"{day_start.date()} we start with {first_prod['ProduktaNosaukums']} and first pallet is produced at {first_prod['first_pallet']}.")
        msg.append(f"Today's production efficiency is {overall_efficiency*100:.2f}%")
    else:
        msg.append("No production registered today yet.")
    return '\n'.join(msg)

def compose_midday_message(report_hour):
    msg = [f"{day_start.date()} production status at {report_hour:02d}:00:"]
    for prod in today_summary:
        msg.append(f"- {prod['ProduktaNosaukums']}: {prod['pallets']} pallets produced")
    msg.append(f"Today's production efficiency up till now is {overall_efficiency*100:.2f}%")
    return '\n'.join(msg)

# --- SENDING LOGIC ---
client = TelegramClient(session_name, api_id, api_hash)
client.start()
group = client.get_entity(group_link)

# Determine which report to send
if now.time() >= time(8,0) and now.time() < time(8,5):
    # 8:00 report
    message = compose_8am_message()
elif now.time() >= time(12,0) and now.time() < time(12,5):
    message = compose_midday_message(12)
elif now.time() >= time(15,0) and now.time() < time(15,5):
    message = compose_midday_message(15)
elif now.time() >= time(18,0) and now.time() < time(18,5):
    message = compose_midday_message(18)
elif now.time() >= time(0,30) and now.time() < time(0,35):
    message = compose_midday_message(0)
else:
    message = None

if message:
    client.send_message(group, message)
    print("Sent to Telegram:\n", message)
else:
    print("No scheduled message to send at this time.")

client.disconnect()








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