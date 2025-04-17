# Import required modules:
# - mysql.connector: for connecting to the MySQL database.
# - BytesIO: for in-memory binary streams used to hold image data.
# - barcode, ImageWriter: for generating a barcode image.
# - PIL (Python Imaging Library) modules: for image creation and drawing.
# - datetime: working with dates and times.
# - win32print, win32ui, ImageWin: modules to interface with Windows printers.
# - threading: to run print operations on a periodic timer.
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
  """
  Splits the given text into multiple lines so that each line does not exceed max_width.
  
  Parameters:
    text (str): The input text to be wrapped.
    font (ImageFont): The font used for measuring text width.
    max_width (int): The maximum allowed width for a line.
    draw (ImageDraw.Draw): The drawing object used to measure text length.
    
  Returns:
    list[str]: A list of strings (lines) that are wrapped appropriately.
  """
  words = text.split()       # Split text into individual words.
  lines = []                 # List to store the final lines.
  current_line = []          # Temporary list to hold words for the current line.

  for word in words:
    # Create a test line by adding the next word to the current line.
    test_line = ' '.join(current_line + [word])
    # Determine the width of the test line using the provided font.
    width = draw.textlength(test_line, font=font)
    if width <= max_width:
      # If within max_width, add the word to the current line.
      current_line.append(word)
    else:
      # If adding the word exceeds max_width, save the current line and start a new one.
      lines.append(' '.join(current_line))
      current_line = [word]

  if current_line:
    # Add any remaining words as the last line.
    lines.append(' '.join(current_line))

  return lines

def printit():
  """
  Main function for printing labels. This function is scheduled to run periodically (every 15 seconds).
  
  It connects to the database, retrieves the latest 10 'paletes' entries,
  checks if they have not been printed, retrieves additional product data,
  generates label images (including a barcode), and prints them using Windows printer interfaces.
  Finally, it updates the database to mark the labels as printed.
  """
  # Schedule the printit function to run every 15 seconds (using threading.Timer)
  threading.Timer(15.0, printit).start()

  # Connect to the MySQL database with no password on localhost for user "root".
  conn = mysql.connector.connect(
    host="127.0.0.1",
    user="root",
    password=""
  )

  print(conn)  # Print the connection object for debugging purposes.

  # SQL query to select the latest 10 records from the 'paletes' table, ordered by date/time (DatumsLaiks) descending.
  sql = "select * from venden.paletes ORDER BY DatumsLaiks DESC LIMIT 10"
  cursor = conn.cursor()
  cursor.execute(sql)
  InitCheck = cursor.fetchall()  # Retrieve all selected records.

  if InitCheck:
    for i in range(0, len(InitCheck)):
      # Check if the label has not been printed (IsPrinted flag; here it seems column index 6 equals 0 for not printed).
      if InitCheck[i][6] == 0:
        # Retrieve product details from 'produkti' table using ProduktaNr matching the current paletes entry.
        cursor = conn.cursor()
        cursor.execute("select * from venden.produkti WHERE ProduktaNr = (%s)", (str(InitCheck[i][0]),))
        ProductData = cursor.fetchall()

        # Calculate the sequential number for this pallet based on creation time
        # Count how many pallets of the same product were created earlier on the same calendar day
        cursor = conn.cursor()
        cursor.execute(
          """
          SELECT COUNT(*) + 1 FROM venden.paletes 
          WHERE DATE(DatumsLaiks) = DATE(%s) 
          AND ProduktaNr = %s 
          AND DatumsLaiks < %s
          """,
          (InitCheck[i][3], str(InitCheck[i][0]), InitCheck[i][3]))
        SequentialNumber = cursor.fetchone()[0]

        # Prepare label details from the retrieved product data:
        # Replace dot with comma for volume and append " L".
        Tilpums = str(ProductData[0][2]).replace(".", ",") + " L"
        # Calculate packaging details as count x packaging size.
        Iepakojumi = str(int(ProductData[0][14] / ProductData[0][12])) + "x" + str(int(ProductData[0][12]))
        Daudzums = str(int(ProductData[0][14]))
        Razotajs = "SIA VENDEN"  # Hardcoded manufacturer name.
        PaletesNr = str(SequentialNumber)
        Svitrkods = str(ProductData[0][18])
        Nosaukums = str(ProductData[0][19])
        # Format the expiry date or production term from YYYY-MM-DD to DD/MM/YYYY.
        Termins_sep = str(InitCheck[i][5]).split("-")
        Termins = Termins_sep[2] + "/" + Termins_sep[1] + "/" + Termins_sep[0]

        # Generate the barcode image:
        # 1. Create an in-memory bytes stream.
        rv = BytesIO()
        # 2. Use the EAN13 barcode generator with the Svitrkods and ImageWriter.
        EAN13(Svitrkods, writer=ImageWriter()).write(rv, options={
          'module_height': 26.0, 'module_width': 0.5,
          'font_size': 16, 'text_distance': 7
        })
        Barcode_image = Image.open(rv)  # Open the barcode image from the stream.

        # Define label image dimensions.
        image_width = 1772
        image_height = 1181
        # Calculate percentage values to assist in positioning text and images.
        image_width_percent = image_width / 100
        image_height_percent = image_height / 100

        # Create a new blank white image.
        img = Image.new('RGB', (image_width, image_height), (255, 255, 255))
        draw = ImageDraw.Draw(img)

        # Draw static headers at the top of the label.
        draw.text((int(image_width_percent * 13), int(image_height_percent * 7)), "Tilpums",
                  font=ImageFont.truetype("calibrib.ttf", size=70), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 45), int(image_height_percent * 7)), "Iepakojumu skaits",
                  font=ImageFont.truetype("calibrib.ttf", size=70), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 82), int(image_height_percent * 7)), "Daudzums gab.",
                  font=ImageFont.truetype("calibrib.ttf", size=70), fill=(0, 0, 0), anchor="mm")

        # Draw the dynamic product details below the headers.
        draw.text((int(image_width_percent * 13), int(image_height_percent * 18)), Tilpums,
                  font=ImageFont.truetype("calibrib.ttf", size=130), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 45), int(image_height_percent * 18)), Iepakojumi,
                  font=ImageFont.truetype("calibrib.ttf", size=130), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 82), int(image_height_percent * 18)), Daudzums,
                  font=ImageFont.truetype("calibrib.ttf", size=130), fill=(0, 0, 0), anchor="mm")

        # Draw manufacturer and palette number details near the bottom.
        draw.text((int(image_width_percent * 13), int(image_height_percent * 94)), "Ražotājs:",
                  font=ImageFont.truetype("calibrib.ttf", size=70), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 36), int(image_height_percent * 94)), Razotajs,
                  font=ImageFont.truetype("calibrib.ttf", size=90), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 72), int(image_height_percent * 94)), "PALETES NR.",
                  font=ImageFont.truetype("calibrib.ttf", size=70), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 88), int(image_height_percent * 94)), PaletesNr,
                  font=ImageFont.truetype("calibrib.ttf", size=170), fill=(0, 0, 0), anchor="mm")

        # Paste the previously generated barcode image into the label.
        img.paste(Barcode_image, (int(image_width_percent * 2), int(image_height_percent * 29)))

        # Draw realization (or expiry) date section.
        draw.text((int(image_width_percent * 22), int(image_height_percent * 70)), "Realizācijas termiņš",
                  font=ImageFont.truetype("calibrib.ttf", size=70), fill=(0, 0, 0), anchor="mm")
        draw.text((int(image_width_percent * 22), int(image_height_percent * 80)), Termins,
                  font=ImageFont.truetype("calibrib.ttf", size=130), fill=(0, 0, 0), anchor="mm")

        # Wrap the product name (Nosaukums) text if needed:
        wrapped_lines = wrap_text(Nosaukums, ImageFont.truetype("calibrib.ttf", size=100), 650, draw)
        NosaukumsWrapped = ""
        for line in wrapped_lines:
          NosaukumsWrapped += line + "\n"

        # Draw the wrapped product name at the appropriate position.
        draw.multiline_text((int(image_width_percent * 72), int(image_height_percent * 60)), NosaukumsWrapped,
                            font=ImageFont.truetype("calibrib.ttf", size=100), fill="black", spacing=40, anchor="mm",
                            align='center')

        # Draw several lines/rectangles as design elements or borders on the label.
        draw.rectangle(((450, 0), (460, 300)), fill="black")
        draw.rectangle(((1150, 0), (1160, 300)), fill="black")
        draw.rectangle(((0, 290), (1772, 300)), fill="black")
        draw.rectangle(((0, 1010), (1772, 1020)), fill="black")
        draw.rectangle(((970, 1021), (980, 1181)), fill="black")
        draw.rectangle(((790, 300), (800, 1018)), fill="black")
        draw.rectangle(((0, 760), (790, 770)), fill="black")
        
        # Save the created label image as "label.png" and then close the image objects.
        img.save("label.png", "PNG")
        img.close()
        Barcode_image.close()

        # Get the default printer name via win32print.
        printer_name = win32print.GetDefaultPrinter()
        file_name = "label.png"

        # Prepare the printer device context (DC) to send the image.
        hDC = win32ui.CreateDC()
        hDC.CreatePrinterDC(printer_name)

        # Open the label image for printing.
        bmp = Image.open(file_name)

        # Start the print job:
        hDC.StartDoc(file_name)
        hDC.StartPage()

        # Convert the image to a device-independent bitmap and send it to the printer.
        dib = ImageWin.Dib(bmp)
        dib.draw(hDC.GetHandleOutput(), (0, 0, image_width, image_height))

        # End the first printed page.
        hDC.EndPage()
        # Start a second page with the same content (appears to be intentional duplication).
        hDC.StartPage()
        dib = ImageWin.Dib(bmp)
        dib.draw(hDC.GetHandleOutput(), (0, 0, image_width, image_height))
        hDC.EndPage()
        hDC.EndDoc()
        hDC.DeleteDC()  # Clean up the device context.
        bmp.close()

        # Check if the product data includes "EKSPORTS" in column index 11:
        if "EKSPORTS" in str(ProductData[0][11]):
          # If true, prepare to print a second image ("eksports.png").
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

        # Mark the current 'paletes' record as printed in the database.
        sql = "UPDATE venden.paletes SET IsPrinted = TRUE WHERE DatumsLaiks = (%s)"
        cursor = conn.cursor()
        cursor.execute(sql, (InitCheck[i][3],))
        conn.commit()
        
        # Print out the label details for debugging and logging.
        print(Tilpums)
        print(Iepakojumi)
        print(Daudzums)
        print(Razotajs)
        print(PaletesNr)
        print(Svitrkods)
        print(Nosaukums)
        print(Termins)
        print("\n")

# Call the printit function to initiate the periodic printing process.
printit()
