#!/usr/bin/env python
# encoding: utf-8
"""
MP3Fetcher.py

Created by Breyten Ernsting on 2010-05-04.
Copyright (c) 2010 __MyCompanyName__. All rights reserved.
"""

import sys
import os
import re
import getopt


help_message = '''
Can have the following parameters:

-h|--help               show this message
-v|--verbose            turn on verbose output
-o|--output <file>      redirect output to file
-u|--url <url>          specify the url to fetch

Expects at least the url to be specified.
'''


class Usage(Exception):
    def __init__(self, msg):
        self.msg = msg


def main(argv=None):
    if argv is None:
        argv = sys.argv
    try:
        try:
            opts, args = getopt.getopt(argv[1:], "ho:vu:", ["help", "output=", "url="])
        except getopt.error, msg:
            raise Usage(msg)
        
        # option processing
        for option, value in opts:
            if option == "-v":
                verbose = True
            if option in ("-h", "--help"):
                raise Usage(help_message)
            if option in ("-o", "--output"):
                output = value
            if option in ("-u", "--url"):
                url = value     
    except Usage, err:
        print >> sys.stderr, sys.argv[0].split("/")[-1] + ": " + str(err.msg)
        print >> sys.stderr, "\t for help use --help"
        return 2
    id3 = shuffler.Id3TagReader()
    id3.fetch(url)

# fixup paths
topdir = os.path.normpath(os.path.join(os.path.abspath(sys.argv[0]), os.pardir, os.pardir))

# then add the lib/ and lib/ext/ paths to sys.path
sys.path.insert(0, os.path.join(topdir, 'lib'))
sys.path.insert(0, os.path.join(topdir, 'lib', 'ext'))

import shuffler

if __name__ == "__main__":
    sys.exit(main())