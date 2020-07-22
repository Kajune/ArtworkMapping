import xml.etree.ElementTree as ET
import matplotlib.pyplot as plt

tree = ET.parse('モネビアンコマップ_202005/xl/drawings/drawing1.xml') 
root = tree.getroot()

ns = '{http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing}'
a = '{http://schemas.openxmlformats.org/drawingml/2006/main}'

damageList = {}

for x in root:
	for y in x.iter(ns + 'sp'):
		if y[0][0].attrib['name'].startswith('テキスト ボックス'):
			off = y[1][0][0].attrib
			ext = y[1][0][1].attrib

			id = None
			pos = None
			for i, z in enumerate(y[3][2].iter(a + 'r')):
				if z[1].text == '・':
					print('dot: ' + z[1].text)
				elif z[1].text.isdecimal():
					id = int(z[1].text)

			if id is None or pos is None:
				continue

			damageList[id] = pos
