Simple File Sharing
===================

This project implements a simple PHP server for sharing files with others over an standard 
web server. Just drop the files in a folder on your Apache and you should be good to go.

Because this is work in progress, the server so far only implements the downloading part of 
file sharing. Uploading has to be performed through administrative access to your web 
server. I use SSH for now, until I get around to implementing a nice upload interface.

This work is licensed under the [GNU AGPL v3](http://www.gnu.org/licenses/agpl-3.0.html) or 
higher.

Query String Commands
---------------------

The server works by reacting to various commands which are passed in the query string 
portion of the URL:

**download**  
Add a `Content-Disposition` header so that the browser downloads the file instead of 
displaying it.
