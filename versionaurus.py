import urllib.request
import codecs
import json
from sys import argv as args
import os

DINO_FILE = __file__[:-len(os.path.basename(__file__))] + "dinos.json"

def online_update():
	url = 'https://en.wikipedia.org/wiki/List_of_dinosaur_genera'
	response = urllib.request.urlopen(url)
	data = response.read()
	text = str(data, 'utf8')
	lines = text.splitlines()
	dinosaurs = [0]
	line_prefix = '<li><i><a href="/wiki/'
	for line in lines:
		if line.startswith(line_prefix):
			line = line[len(line_prefix):]
			line = line[line.index('>') + 1:line.index('<')]
			if '-' in line:
				line = line[:line.index('-')]
			dinosaurs.append(line)
	with open(DINO_FILE, 'wt') as f:
		json.dump(dinosaurs, f)
	print('Wow! Dowloaded %d dinos' % len(dinosaurs))		

def load_dinos():
	with open(DINO_FILE, 'rt') as f:
		dinos = json.load(f)
	return dinos

def print_help():
	print('Parameters:')
	print('--update - causes script to autoupdate databse from wikipedia')
	print('Usage:')
	print('script.py [--update] [last version]')
	print('if last version is specified, next version is thrown.')
	print('otherwise is printed first version')	

if '-h' in args or '--help' in args or '/?' in args or '/h' in args:
	print_help()
	
if '--update' in args:
	online_update()
	exit(0)

if not os.path.isfile(DINO_FILE):
	print('Running for first time, downloading database')
	online_update()
	
dinos = load_dinos()
last_version = dict(enumerate(args)).get(1, 0)

if last_version not in dinos:
	print('Sorry, I tried, but havent foudnd "%s" in my database' % last_version)
	exit(0)

last_version_index = dinos.index(last_version)
if last_version_index + 1 >= len(dinos):
	print('You are too fast developer. No more version for you :-(')
	exit(0)
	
print(dinos[last_version_index + 1])
