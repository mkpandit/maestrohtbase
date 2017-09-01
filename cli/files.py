import subprocess

def change_text_in_file(old_string, new_string, file_path):
	'Change text on file'

	subprocess.call(['sed', '-i', '/%s/c\\%s' % (old_string, new_string), file_path])
	return

def line_prepender(filename, line):
    with open(filename, 'r+') as f:
        content = f.read()
        f.seek(0, 0)
        f.write(line.rstrip('\r\n') + '\n' + content)

def line_append(file_name, line):
	'write text on file'
	
	write_file = open(file_name, 'a+')
	write_file.write(line + '\n')
	write_file.close()
	return

def write_on_file(file_name, text_find, text_write):
	'write text on file'
	
	find = False

	write_file = open(file_name, 'r+')
	lines = write_file.readlines()
	write_file.seek(0)
	for line in lines:
		if text_find in line:
			find = True
		write_file.write(line)
	if not find:
		write_file.write(text_write)
	write_file.truncate()
	write_file.close()
	return find

def delete_on_file(file_name, text_find):
	'remove text on file'
	
	find = False
	write_file = open(file_name, 'r+')
	lines = write_file.readlines()
	write_file.seek(0)
	for line in lines:
		if not text_find in line:
			write_file.write(line)
		else: find = True
	write_file.truncate()
	write_file.close()

	return find
