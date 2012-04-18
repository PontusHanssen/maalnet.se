#!/usr/bin/python2
import tmdb
import _mysql
import sys


tmdb.configure("0ed82318c3b01c2800a6fcffdc6d2d34")

titel = sys.argv[1]
url = sys.argv[2]

movie = tmdb.tmdb(titel)

def GetTitle(movie):
	return movie.getName(0)
def GetRating(movie):
	return movie.getRating(0)
def GetImage(movie):
	return movie.getPoster(0,"c")
def GetDescription(movie):
	return movie.getOverview(0).replace("'","\\'")

db=_mysql.connect(host="localhost", user="root", passwd=":-)", db="filmer")
query = "INSERT INTO film (titel,bild,beskrivning,rating,url) VALUES('"+ GetTitle(movie) + "', '" + GetImage(movie) + "','" + GetDescription(movie) +"','" + GetRating(movie) + "', '" + url + "')"
db.query(query)

