import MySQLdb
import os
from exportExcel import saveExcel

if __name__ == '__main__':
	sql = MySQLdb.connect(
		user=os.environ['MYSQL_USER'], passwd=os.environ['MYSQL_PASSWORD'],
		host=os.environ['MYSQL_HOST'], db=os.environ['MYSQL_DATABASE'],
		charset='utf8')

	cur = sql.cursor()
	cur.execute("SET NAMES utf8")

	query = "SELECT id FROM artwork WHERE `deleted`=0"
	cur.execute(query)
	artwork_indices = cur.fetchall()

	for id in artwork_indices:
		print(saveExcel(id[0]))
