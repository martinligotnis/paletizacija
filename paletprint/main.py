import mysql.connector
from io import BytesIO
from barcode import EAN13
from barcode.writer import ImageWriter
from PIL import Image, ImageDraw, ImageFont, ImageWin
import datetime
import win32print
import win32ui
import threading

def wrap_text(text, font, max_width, draw):

  words = text.split()
  lines = []
  current_line = []

  for word in words:
    test_line = ' '.join(current_line + [word])
    width = draw.textlength(test_line, font=font)
    if width <= max_width:
      current_line.append(word)
    else:
      lines.append(' '.join(current_line))
      current_line = [word]

  if current_line:
    lines.append(' '.join(current_line))

  return lines


def printit():
  threading.Timer(15.0, printit).start()

  conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password=""
  )

  print(conn)

  sql = "select * from venden.paletes ORDER BY DatumsLaiks DESC LIMIT 10"
  cursor = conn.cursor()
  cursor.execute(sql)
  InitCheck = cursor.fetchall()

  if InitCheck:
    for i in range(0, len(InitCheck)):
      if InitCheck[i][6] == 0:
        cursor = conn.cursor()
        cursor.execute("select * from venden.produkti WHERE ProduktaNr = (%s)", (str(InitCheck[i][0]),))
        ProductData = cursor.fetchall()
        cursor = conn.cursor()
        cursor.execute(
          "select COUNT(*) from venden.paletes WHERE DatumsLaiks >= CAST((%s) as date) AND ProduktaNr = (%s)",
          (InitCheck[i][3], str(InitCheck[i][0]),))
        ExistCount = cursor.fetchall()

        Tilpums = str(ProductData[0][2]).replace(".", ",") + " L"
        Iepakojumi = str(int(ProductData[0][14] / ProductData[0][12])) + "x" + str(int(ProductData[0][12]))
        Daudzums = str(int(ProductData[0][14]))
        Razotajs = "SIA VENDEN"
        PaletesNr = str(ExistCount[0][0])
        Svitrkods = str(ProductData[0][18])
        Nosaukums = str(ProductData[0][19])
        Termins_sep = str(InitCheck[i][5]).split("-")
        Termins = Termins_sep[2] + "/" + Termins_sep[1] + "/" + Termins_sep[0]

        # Write to a file-like object:
        rv = BytesIO()
        EAN13(Svitrkods, writer=ImageWriter()).write(rv, options={'module_height': 26.0, 'module_width': 0.5,
                                                                  'font_size': 16, 'text_distance': 7})
        Barcode_image = Image.open(rv)

        image_width = 1772
        image_height = 1181
        image_width_percent = image_width / 100
        image_height_percent = image_height / 100
        img = Image.new('RGB', (image_width, image_height), (255, 255, 255))
        draw = ImageDraw.Draw(img)
        draw.text((int(image_width_percent * 13), int(image_height_percent * 7)), "Tilpums",
                  font=(ImageFont.truetype("calibrib.ttf", size=70)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 45), int(image_height_percent * 7)), "Iepakojumu skaits",
                  font=(ImageFont.truetype("calibrib.ttf", size=70)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 82), int(image_height_percent * 7)), "Daudzums gab.",
                  font=(ImageFont.truetype("calibrib.ttf", size=70)), fill=(0, 0, 0), anchor="mm")

        draw.text((int(image_width_percent * 13), int(image_height_percent * 18)), Tilpums,
                  font=(ImageFont.truetype("calibrib.ttf", size=130)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 45), int(image_height_percent * 18)), Iepakojumi,
                  font=(ImageFont.truetype("calibrib.ttf", size=130)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 82), int(image_height_percent * 18)), Daudzums,
                  font=(ImageFont.truetype("calibrib.ttf", size=130)), fill=(0, 0, 0), anchor="mm")

        draw.text((int(image_width_percent * 13), int(image_height_percent * 94)), "Ražotājs:",
                  font=(ImageFont.truetype("calibrib.ttf", size=70)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 36), int(image_height_percent * 94)), Razotajs,
                  font=(ImageFont.truetype("calibrib.ttf", size=90)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 72), int(image_height_percent * 94)), "PALETES NR.",
                  font=(ImageFont.truetype("calibrib.ttf", size=70)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 88), int(image_height_percent * 94)), PaletesNr,
                  font=(ImageFont.truetype("calibrib.ttf", size=170)), fill=(0, 0, 0), anchor="mm")

        img.paste(Barcode_image, (int(image_width_percent * 2), int(image_height_percent * 29)))

        draw.text((int(image_width_percent * 22), int(image_height_percent * 70)), "Realizācijas termiņš",
                  font=(ImageFont.truetype("calibrib.ttf", size=70)), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 22), int(image_height_percent * 80)), Termins,
                  font=(ImageFont.truetype("calibrib.ttf", size=130)), fill=(0, 0, 0), anchor="mm")

        wrapped_lines = wrap_text(Nosaukums, ImageFont.truetype("calibrib.ttf", size=100), 650, draw)
        NosaukumsWrapped = ""
        for line in wrapped_lines:
          NosaukumsWrapped += line + "\n"

        draw.multiline_text((int(image_width_percent * 72), int(image_height_percent * 60)), NosaukumsWrapped,
                            font=ImageFont.truetype("calibrib.ttf", size=100), fill="black", spacing=40, anchor="mm",
                            align='center')

        draw.rectangle(((450, 0), (460, 300)), fill="black")
        draw.rectangle(((1150, 0), (1160, 300)), fill="black")
        draw.rectangle(((0, 290), (1772, 300)), fill="black")
        draw.rectangle(((0, 1010), (1772, 1020)), fill="black")
        draw.rectangle(((970, 1021), (980, 1181)), fill="black")
        draw.rectangle(((790, 300), (800, 1018)), fill="black")
        draw.rectangle(((0, 760), (790, 770)), fill="black")
        img.save("label.png", "PNG")
        img.close()
        Barcode_image.close()

        printer_name = win32print.GetDefaultPrinter()
        file_name = "label.png"

        hDC = win32ui.CreateDC()
        hDC.CreatePrinterDC(printer_name)

        bmp = Image.open(file_name)

        hDC.StartDoc(file_name)
        hDC.StartPage()

        dib = ImageWin.Dib(bmp)
        dib.draw(hDC.GetHandleOutput(), (0, 0, image_width, image_height))

        hDC.EndPage()
        hDC.StartPage()

        dib = ImageWin.Dib(bmp)
        dib.draw(hDC.GetHandleOutput(), (0, 0, image_width, image_height))

        hDC.EndPage()
        hDC.EndDoc()
        hDC.DeleteDC()
        bmp.close()

        if "EKSPORTS" in str(ProductData[0][11]):
          file_name = "eksports.png"

          hDC = win32ui.CreateDC()
          hDC.CreatePrinterDC(printer_name)

          bmp = Image.open(file_name)

          hDC.StartDoc(file_name)
          hDC.StartPage()

          dib = ImageWin.Dib(bmp)
          dib.draw(hDC.GetHandleOutput(), (0, 0, image_width, image_height))

          hDC.EndPage()
          hDC.StartPage()

          dib = ImageWin.Dib(bmp)
          dib.draw(hDC.GetHandleOutput(), (0, 0, image_width, image_height))

          hDC.EndPage()
          hDC.EndDoc()
          hDC.DeleteDC()
          bmp.close()


        sql = "UPDATE venden.paletes SET IsPrinted = TRUE WHERE DatumsLaiks = (%s)"
        cursor = conn.cursor()
        cursor.execute(sql, (InitCheck[i][3],))
        conn.commit()
        
        print(Tilpums)
        print(Iepakojumi)
        print(Daudzums)
        print(Razotajs)
        print(PaletesNr)
        print(Svitrkods)
        print(Nosaukums)
        print(Termins)

        print("\n")

printit()