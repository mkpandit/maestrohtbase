# python-nmap
# v0.1.0
VERSION=`python setup.py --version`
ARCHIVE=`python setup.py --fullname`
FILES=example.py setup.py README.txt CHANGELOG Makefile gpl-3.0.txt 


test:
	@python nmap/nmap.py

install:
	@python setup.py install

archive:
	@python setup.py sdist
	@echo Archive is create and named dist/$(ARCHIVE).tar.gz
	@echo -n md5sum is :
	@md5sum dist/$(ARCHIVE).tar.gz

license:
	@python setup.py --license

register:
	@python setup.py register
