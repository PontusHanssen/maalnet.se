#!/usr/bin/env python2

import tvdb_api
import _mysql
import sys
import os

title = sys.argv[1]
season = sys.argv[2]
episode = sys.argv[3]
url = sys.argv[4]

T = tvdb_api.Tvdb()
Episode = T[title][int(season)][int(episode)]
Show = T[title]

os.system('wget ' + Show['poster'] + ' -O "images/poster-' + title + '.jpg"')
os.system('wget ' + Show['banner'] + ' -O "images/banner-' + title + '.jpg"')
poster = "images/poster-" + title + ".jpg"
banner = "images/banner-" + title + ".jpg"
print(Show['poster'])

db=_mysql.connect(host="localhost", user="root", passwd=":-)", db="projekt")
query = "INSERT INTO serier (showname,season,episode,title,image,banner,path) VALUES('"+ title + "', " + season + ", " + episode + ", '" + Episode['episodename'].replace("'","") + "', '" + poster + "', '" + banner + "', '" + url + "')"
db.query(query)



