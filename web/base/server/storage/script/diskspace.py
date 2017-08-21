#!/usr/bin/env python
# vim: noexpandtab shiftwidth=4 softtabstop=4 tabstop=4

import socket
import struct
import time
import traceback
import urllib
import cgi
import cgitb
import sys, json
import re
import subprocess

PROTO_BASE = 0

CLTOMA_CSERV_LIST = (PROTO_BASE+500)
MATOCL_CSERV_LIST = (PROTO_BASE+501)
CLTOCS_HDD_LIST_V1 = (PROTO_BASE+502)
CSTOCL_HDD_LIST_V1 = (PROTO_BASE+503)
CLTOMA_SESSION_LIST = (PROTO_BASE+508)
MATOCL_SESSION_LIST = (PROTO_BASE+509)
CLTOMA_INFO = (PROTO_BASE+510)
MATOCL_INFO = (PROTO_BASE+511)
CLTOMA_FSTEST_INFO = (PROTO_BASE+512)
MATOCL_FSTEST_INFO = (PROTO_BASE+513)
CLTOMA_CHUNKSTEST_INFO = (PROTO_BASE+514)
MATOCL_CHUNKSTEST_INFO = (PROTO_BASE+515)
CLTOMA_CHUNKS_MATRIX = (PROTO_BASE+516)
MATOCL_CHUNKS_MATRIX = (PROTO_BASE+517)
CLTOMA_EXPORTS_INFO = (PROTO_BASE+520)
MATOCL_EXPORTS_INFO = (PROTO_BASE+521)
CLTOMA_MLOG_LIST = (PROTO_BASE+522)
MATOCL_MLOG_LIST = (PROTO_BASE+523)
CLTOMA_CSSERV_REMOVESERV = (PROTO_BASE+524)
MATOCL_CSSERV_REMOVESERV = (PROTO_BASE+525)
CLTOCS_HDD_LIST_V2 = (PROTO_BASE+600)
CSTOCL_HDD_LIST_V2 = (PROTO_BASE+601)
LIZ_CLTOMA_CHUNKS_HEALTH = 1526
LIZ_MATOCL_CHUNKS_HEALTH = 1527
LIZ_CLTOMA_LIST_GOALS = 1547
LIZ_MATOCL_LIST_GOALS = 1548
LIZ_CLTOMA_CSERV_LIST = 1549
LIZ_MATOCL_CSERV_LIST = 1550
LIZ_CLTOMA_METADATASERVERS_LIST = 1522
LIZ_MATOCL_METADATASERVERS_LIST = 1523
LIZ_CLTOMA_METADATASERVER_STATUS = 1545
LIZ_MATOCL_METADATASERVER_STATUS = 1546
LIZ_CLTOMA_HOSTNAME = 1551
LIZ_MATOCL_HOSTNAME = 1552

LIZARDFS_VERSION_WITH_QUOTAS = (2, 5, 0)
LIZARDFS_VERSION_WITH_CUSTOM_GOALS = (2, 5, 3)
LIZARDFS_VERSION_WITH_LIST_OF_SHADOWS = (2, 5, 5)

cgitb.enable()

fields = cgi.FieldStorage()

try:
	if fields.has_key("masterhost"):
		masterhost = fields.getvalue("masterhost")
	else:
		masterhost = '127.0.0.1'
except Exception:
	masterhost = '127.0.0.1'
try:
	masterport = int(fields.getvalue("masterport"))
except Exception:
	masterport = 9421
try:
	if fields.has_key("mastername"):
		mastername = fields.getvalue("mastername")
	else:
		mastername = 'LizardFS'
except Exception:
	mastername = 'LizardFS'

thsep = ''
html_thsep = ''
CHARTS_CSV_CHARTID_BASE = 90000

############################
# Deserialization framework

# Elements used to build tree which describes a structure of data to deserialize.
# Examples of such trees:
# String + Primitive("Q") -- a pair: string and uint64_t
# List(String) -- a list of strings
# List(List(String)) -- a list of lists of strings
# List(3 * String) -- a list of tuples (string, string, string)
# Tuple("LQBB") -- a tuple consising of uint32_t, uint64_t, uint8_t, uint8_t
# List(Tuple(3 * "L")) -- a list of 3-tuples of uint32_t
# List(Tuple("LLL") + String) -- a list of (3-tuple of uint32_t, string)
# Dict(Primitive("Q", List(String)) -- a dict uint64_t -> list of strings

def Primitive(format):
	return ("primitive", format)
def Tuple(format):
	return ("tuple", format)
String = ("string",)
def List(element_tree):
	return ("list", element_tree)
def Dict(key_tree, value_tree):
	return ("dict", key_tree, value_tree)

def deserialize(buffer, tree, return_tuple=False):
	""" Deserialize (and remove) data from a buffer described by a tree
	buffer - a bytearray with serialized data
	tree   - a structure of the data built using nodes like 'List', 'Primitive', ...
	return_tuple - if True, returns tuple (even 1-tuple) instead of a value
	"""
	head_len = 2 # Number of elements in a tree to be removed after deserializing first entry
	if tree[0] == "primitive":
		head = deserialize_primitive(buffer, tree[1])
	elif tree[0] == "tuple":
		head = deserialize_tuple(buffer, tree[1])
	elif tree[0] == "string":
		head = deserialize_string(buffer)
		head_len = 1
	elif tree[0] == "list":
		head = deserialize_list(buffer, tree[1])
	elif tree[0] == "dict":
		head = deserialize_dict(buffer, tree[1], tree[2])
		head_len = 3
	else:
		raise RuntimeError, "Unknown tree to deserialize: {0}".format(tree)
	if (len(tree) > head_len):
		tail = deserialize(buffer, tree[head_len:], True)
		return (head,) + tail
	else:
		return (head,) if return_tuple else head

# Deserialization functions for tree nodes

def deserialize_primitive(buffer, format):
	""" Deserialize a single value described in format string and remove it from buffer """
	ret, = deserialize_tuple(buffer, format)
	return ret

def deserialize_tuple(bytebuffer, format):
	""" Deserialize a tuple described in format string and remove it from buffer """
	size = struct.calcsize(">" + format)
	ret = struct.unpack_from(">" + format, buffer(bytebuffer))
	del bytebuffer[0:size]
	return ret

def deserialize_string(buffer):
	""" Deserialize a std::string and remove it from buffer """
	length = deserialize_primitive(buffer, "L")
	if len(buffer) < length or buffer[length - 1] != 0:
		raise RuntimeError, "malformed message; cannot deserialize"
	ret = str(buffer[0:length-1])
	del buffer[0:length]
	return ret

def deserialize_list(buffer, element_tree):
	""" Deserialize a list of elements and remove it from buffer """
	length = deserialize_primitive(buffer, "L")
	return [deserialize(buffer, element_tree) for i in xrange(length)]

def deserialize_dict(buffer, key_tree, value_tree):
	""" Deserialize a dict and remove it from buffer """
	length = deserialize_primitive(buffer, "L")
	ret = {}
	for i in xrange(length):
		key = deserialize(buffer, key_tree)
		val = deserialize(buffer, value_tree)
		ret[key] = val
	return ret

##########################
# Serialization framework

def make_liz_message(type, version, data):
	""" Adds a proper header to message data """
	return struct.pack(">LLL", type, len(data) + 4, version) + data

#####################################
# Implementation of network messages

def cltoma_list_goals():
	if masterversion < LIZARDFS_VERSION_WITH_CUSTOM_GOALS:
		# For old servers just return the default 10 goals
		return [(i, str(i), str(i) + "*_") for i in xrange(1, 10)]
	else:
		# For new servers, use LIZ_CLTOMA_LIST_GOALS to fetch the list of goal definitions
		request = make_liz_message(LIZ_CLTOMA_LIST_GOALS, 0, "\1")
		response = send_and_receive(masterhost, masterport, request, LIZ_MATOCL_LIST_GOALS, 0)
		goals = deserialize(response, List(Primitive("H") + 2 * String))
		if response:
			raise RuntimeError, "malformed LIZ_MATOCL_LIST_GOALS response (too long by {0} bytes)".format(len(response))
		return goals

def cltoma_chunks_health(only_regular):
	goals = cltoma_list_goals()
	request = make_liz_message(LIZ_CLTOMA_CHUNKS_HEALTH, 0, struct.pack(">B", only_regular))
	response = send_and_receive(masterhost, masterport, request, LIZ_MATOCL_CHUNKS_HEALTH, 0)
	regular_only = deserialize(response, Primitive("B"))
	safe, endangered, lost = deserialize(response, 3 * Dict(Primitive("B"), Primitive("Q")))
	raw_replication, raw_deletion = deserialize(response, 2 * Dict(Primitive("B"), Tuple(11 * "Q")))
	if response:
		raise RuntimeError, "malformed LIZ_MATOCL_CHUNKS_HEALTH response (too long by {0} bytes)".format(len(response))
	availability, replication, deletion = [], [], []
	for (id, name, _) in goals:
		availability.append((name, safe.setdefault(id, 0), endangered.setdefault(id, 0), lost.setdefault(id, 0)))
		replication.append((name,) + raw_replication.setdefault(id, 11 * (0,)))
		deletion.append((name,) + raw_deletion.setdefault(id, 11 * (0,)))
	return (availability, replication, deletion)

def cltoma_hostname(host, port):
	request = make_liz_message(LIZ_CLTOMA_HOSTNAME, 0, "")
	response = send_and_receive(host, port, request, LIZ_MATOCL_HOSTNAME, 0)
	return deserialize(response, String)

def cltoma_metadataserver_status(host, port):
	LIZ_METADATASERVER_STATUS_MASTER = 1
	LIZ_METADATASERVER_STATUS_SHADOW_CONNECTED = 2
	LIZ_METADATASERVER_STATUS_SHADOW_DISCONNECTED = 3
	request = make_liz_message(LIZ_CLTOMA_METADATASERVER_STATUS, 0, struct.pack(">L", 0))
	response = send_and_receive(host, port, request, LIZ_MATOCL_METADATASERVER_STATUS, 0)
	_, status, metadata_version = deserialize(response, Tuple("LBQ"))
	if status == LIZ_METADATASERVER_STATUS_MASTER:
		return ("master", "running", metadata_version)
	elif status == LIZ_METADATASERVER_STATUS_SHADOW_CONNECTED:
		return ("shadow", "connected", metadata_version)
	elif status == LIZ_METADATASERVER_STATUS_SHADOW_DISCONNECTED:
		return ("shadow", "disconnected", metadata_version)
	else:
		return ("(unknown)", "(unknown)", metadata_version)

def cltoma_metadataservers_list():
	request = make_liz_message(LIZ_CLTOMA_METADATASERVERS_LIST, 0, "")
	response = send_and_receive(masterhost, masterport, request, LIZ_MATOCL_METADATASERVERS_LIST, 0)
	_, shadows = deserialize(response, Primitive("L") + List(Tuple("LHHBB")))
	servers = [(masterhost, masterport) + masterversion] + shadows
	ret = []
	for (addr, port, v1, v2, v3) in servers:
		# for shadow masters, addr is int (4 bytes) -- convert it to string.
		# for the active master we use "masterhost" to connect with it and we don't know the real IP
		ip = addr_to_host(addr) if isinstance(addr, (int, long)) else "-"
		version = "%u.%u.%u" % (v1, v2, v3)
		if port == 0:
			# shadow didn't register its port yet
			personality = "shadow"
			host, state, metadata = 3 * ("(unknown)",)
		else:
			# master or a fully registered shadow
			try:
				host = cltoma_hostname(addr, port)
				personality, state, metadata = cltoma_metadataserver_status(addr, port)
			except:
				personality, host, state, metadata = 4 * ("(error)",)

		ret.append((host, ip, port, personality, '<div class="statusstater">'+state+'</div>'))
        print 'Manish: ' + ret
	return ret
    

def make_table_row(cell_begin, cell_end, cell_contents):
	""" Returns a string representation of a html table row
	cell_begin - tag which opens each cell
	cell_end   - tag which ends each cell
	cell_contents - collection of values for the row
	"""
	return "	<tr>" + "".join([cell_begin + str(i) + cell_end for i in cell_contents]) + "</tr>"

def htmlentities(str):
	return str.replace('&','&amp;').replace('<','&lt;').replace('>','&gt;').replace("'",'&apos;').replace('"','&quot;')

def urlescape(str):
	return urllib.quote_plus(str)

def mysend(socket,msg):
	totalsent = 0
	while totalsent < len(msg):
		sent = socket.send(msg[totalsent:])
		if sent == 0:
			raise RuntimeError, "socket connection broken"
		totalsent = totalsent + sent

def myrecv(socket,leng):
	msg = ''
	while len(msg) < leng:
		chunk = socket.recv(leng-len(msg))
		if chunk == '':
			raise RuntimeError, "socket connection broken"
		msg = msg + chunk
	return msg

def addr_to_host(addr):
	""" Convert IP address ('xxx.xxx.xxx.xxx' or 'hostname' or a 4-byte integer) to string """
	if isinstance(addr, (int, long)):
		return socket.inet_ntoa(struct.pack(">L", addr))
	elif isinstance(addr, str):
		return addr
	else:
		raise RuntimeError, "unknown format of server address"


def send_and_receive(host, port, request, response_type, response_version = None):
	""" Sends a request, receives response and verifies its type and (if provided) version """
	s = socket.socket()
	s.connect((addr_to_host(host), port))
	try:
		mysend(s, request)
		header = myrecv(s, 8)
		cmd, length = struct.unpack(">LL", header)
		if cmd != response_type:
			raise RuntimeError, "received wrong response (%x instead of %x)" % (cmd, response_type)
		data = bytearray(myrecv(s, length))
	except:
		s.close()
		raise
	s.close()
	if response_version is not None:
		version = deserialize_primitive(data, "L")
		if version != response_version:
			raise RuntimeError, "received wrong response version (%u instead of %u)" % (version, response_version)
	return data

def decimal_number(number,sep=' '):
	parts = []
	while number>=1000:
		number,rest = divmod(number,1000)
		parts.append("%03u" % rest)
	parts.append(str(number))
	parts.reverse()
	return sep.join(parts)

def humanize_number(number,sep=''):
	number*=100
	scale=0
	while number>=99950:
		number = number//1024
		scale+=1
	if number<995 and scale>0:
		b = (number+5)//10
		nstr = "%u.%u" % divmod(b,10)
	else:
		b = (number+50)//100
		nstr = "%u" % b
	if scale>0:
		return "%s%s%si" % (nstr,sep,"-KMGTPEZY"[scale])
	else:
		return "%s%s" % (nstr,sep)

def timeduration_to_shortstr(timeduration):
	for l,s in ((86400,'d'),(3600,'h'),(60,'m'),(1,'s')):
		if timeduration>=l:
			n = float(timeduration)/float(l)
			rn = round(n,1)
			if n==round(n,0):
				return "%.0f%s" % (n,s)
			else:
				return "%s%.1f%s" % (("~" if n!=rn else ""),rn,s)
	return "0s"

def timeduration_to_fullstr(timeduration):
	if timeduration>=86400:
		days,dayseconds = divmod(timeduration,86400)
		daysstr = "%u day%s, " % (days,("s" if days!=1 else ""))
	else:
		dayseconds = timeduration
		daysstr = ""
	hours,hourseconds = divmod(dayseconds,3600)
	minutes,seconds = divmod(hourseconds,60)
	return "%u second%s (%s%u:%02u:%02u)" % (timeduration,("" if timeduration==1 else "s"),daysstr,hours,minutes,seconds)

# check version
masterversion = (0,0,0)
try:
	s = socket.socket()
	s.connect((masterhost,masterport))
	mysend(s,struct.pack(">LL",CLTOMA_INFO,0))
	header = myrecv(s,8)
	cmd,length = struct.unpack(">LL",header)
	data = myrecv(s,length)
	if cmd==MATOCL_INFO:
		if length==52:
			masterversion = (1,4,0)
		elif length==60:
			masterversion = (1,5,0)
		elif length==68 or length==76:
			masterversion = struct.unpack(">HBB",data[:4])
except Exception:
	print "Content-Type: text/html; charset=UTF-8"
	print
	print """<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">"""
	print """<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">"""
	print """<head>"""
	print """<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />"""
	print """<title>LizardFS Info (%s)</title>""" % (htmlentities(mastername))
	print """<link href="favicon.ico" rel="icon" type="image/x-icon" />"""
	print """<link rel="stylesheet" href="mfs.css" type="text/css" />"""
	print """</head>"""
	print """<body>"""
	print """<h1 align="center">Can't connect to LizardFS master (IP:%s ; PORT:%u)</h1>""" % (htmlentities(masterhost),masterport)
	print """</body>"""
	print """</html>"""
	exit()

if masterversion==(0,0,0):
	print "Content-Type: text/html; charset=UTF-8"
	print
	print """<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">"""
	print """<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">"""
	print """<head>"""
	print """<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />"""
	print """<title>LizardFS Info (%s)</title>""" % (htmlentities(mastername))
	print """<link href="favicon.ico" rel="icon" type="image/x-icon" />"""
	print """<link rel="stylesheet" href="mfs.css" type="text/css" />"""
	print """</head>"""
	print """<body>"""
	print """<h1 align="center">Can't detect LizardFS master version</h1>"""
	print """</body>"""
	print """</html>"""
	exit()


def createlink(update):
	c = []
	for k in fields:
		if k not in update:
			c.append("%s=%s" % (k,urlescape(fields.getvalue(k))))
	for k,v in update.iteritems():
		if v!="":
			c.append("%s=%s" % (k,urlescape(v)))
	return "mfs.cgi?%s" % ("&amp;".join(c))

def createorderlink(prefix,columnid):
	ordername = "%sorder" % prefix
	revname = "%srev" % prefix
	try:
		orderval = int(fields.getvalue(ordername))
	except Exception:
		orderval = 0
	try:
		revval = int(fields.getvalue(revname))
	except Exception:
		revval = 0
	return createlink({revname:"1"}) if orderval==columnid and revval==0 else createlink({ordername:str(columnid),revname:"0"})


if fields.has_key("sections"):
	sectionstr = fields.getvalue("sections")
	sectionset = set(sectionstr.split("|"))
else:
	sectionset = set(("IN",))

if masterversion<(1,5,14):
	sectiondef={
		"IN":"Dashboard",
		"CS":"Files",
		"HD":"Hard Disks",
		"ML":"Mount List",
		"MC":"Master Charts",
		"CC":"Chunk Servers Charts",
		"HELP":"Help"
	}
	sectionorder=["IN","CS","HD","ML","MC","CC","HELP"];
elif masterversion<LIZARDFS_VERSION_WITH_CUSTOM_GOALS:
	sectiondef={
		"IN":"Dashboard",
		"CS":"Servers",
		"HD":"vDisks",
		"EX":"Config",
		"MS":"Mounts",
		"MO":"Operations",
		"MC":"Master",
		"CC":"Client",
		"HELP":"Help"
	}
	sectionorder=["IN","CS","HD","EX","MS","MO","MC","CC","HELP"];
else:
	sectiondef={
		"IN":"Dashboard",
		"CH":"Files",
		"CS":"Servers",
		"HD":"vDisks",
		"EX":"Config",
		"MS":"Mounts",
		"MO":"Operations",
		"MC":"Master",
		"CC":"Client",
		"HELP":"Help"
	}
	sectionorder=["IN","CH","CS","HD","EX","MS","MO","MC","CC","HELP"];

if "IN" in sectionset:
	try:
		INmatrix = int(fields.getvalue("INmatrix"))
	except Exception:
		INmatrix = 0
	try:
		out = []
                space_info = []
		s = socket.socket()
		s.connect((masterhost,masterport))
		mysend(s,struct.pack(">LL",CLTOMA_INFO,0))
		header = myrecv(s,8)
		cmd,length = struct.unpack(">LL",header)
		if cmd==MATOCL_INFO and length==52:
			data = myrecv(s,length)
			total,avail,trspace,trfiles,respace,refiles,nodes,chunks,tdcopies = struct.unpack(">QQQLQLLLL",data)
			print (decimal_number(total),humanize_number(total,"&nbsp;"))
			print (decimal_number(avail),humanize_number(avail,"&nbsp;"))
			print (decimal_number(trspace),humanize_number(trspace,"&nbsp;"))
		elif cmd==MATOCL_INFO and length==60:
			data = myrecv(s,length)
			total,avail,trspace,trfiles,respace,refiles,nodes,dirs,files,chunks,tdcopies = struct.unpack(">QQQLQLLLLLL",data)
			print (decimal_number(total),humanize_number(total,"&nbsp;"))
			print (decimal_number(avail),humanize_number(avail,"&nbsp;"))
			print (decimal_number(trspace),humanize_number(trspace,"&nbsp;"))
		elif cmd==MATOCL_INFO and length==68:
			data = myrecv(s,length)
			v1,v2,v3,total,avail,trspace,trfiles,respace,refiles,nodes,dirs,files,chunks,allcopies,tdcopies = struct.unpack(">HBBQQQLQLLLLLLL",data)
			if masterversion>=(1,6,10):
				out.append("""		<th><a style="cursor:default" title="chunks from 'regular' hdd space and 'marked for removal' hdd space">all chunk copies</a></th>""")
				out.append("""		<th><a style="cursor:default" title="only chunks from 'regular' hdd space">regular chunk copies</a></th>""")
			else:
				out.append("""		<th>chunk copies</th>""")
				out.append("""		<th>copies to delete</th>""")
			print (decimal_number(total),humanize_number(total,"&nbsp;"))
			print (decimal_number(avail),humanize_number(avail,"&nbsp;"))
			print (decimal_number(trspace),humanize_number(trspace,"&nbsp;"))
		elif cmd==MATOCL_INFO and length==76:
			v1,v2,v3,memusage,total,avail,trspace,trfiles,respace,refiles,nodes,dirs,files,chunks,allcopies,tdcopies = struct.unpack(">HBBQQQQLQLLLLLLL",data)
			data = myrecv(s,length)
			
			df = subprocess.Popen("free -m | grep 'Mem' | awk '//{ print $2 }'", shell = True, stdout = subprocess.PIPE) 
			res = df.communicate()[0]
                        space_info.append(humanize_number(total," "))
                        space_info.append(humanize_number(avail," "))
			#print(humanize_number(total," "))
			#print (humanize_number(avail," "))
                        print json.dumps(space_info)
			
		else:
			out.append("""<table class="FRA table table-bordered table-hover toggle-circle tablet breakpoint footable-loaded footable" cellspacing="0">""")
			out.append("""	<tr><td align="left">unrecognized answer from LizardFS master</td></tr>""")
			out.append("""</table>""")
		s.close()
		print "\n".join(out)
	except Exception:
		print """<table class="FRA table table-bordered table-hover toggle-circle tablet breakpoint footable-loaded footable" cellspacing="0" summary="Exception">"""
		print """<tr><td align="left"><pre>"""
		traceback.print_exc(file=sys.stdout)
		print """</pre></td></tr>"""
		print """</table>"""
