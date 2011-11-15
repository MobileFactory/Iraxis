#!/usr/bin/env python
import os
import sys
sockaddr = '/tmp/trac-'+sys.argv[1]+'.sock'
os.environ['TRAC_ENV'] = '/home/www/'+sys.argv[1]

try:
     from trac.web.main import dispatch_request
     import trac.web._fcgi
     fcgiserv = trac.web._fcgi.WSGIServer(dispatch_request, bindAddress = sockaddr, umask = 777)
     fcgiserv.run()

except SystemExit:
    raise
except Exception, e:
    print 'Content-Type: text/plain\r\n\r\n',
    print 'Oops...'
    print
    print 'Trac detected an internal error:'
    print
    print e
    print
    import traceback
    import StringIO
    tb = StringIO.StringIO()
    traceback.print_exc(file=tb)
    print tb.getvalue()