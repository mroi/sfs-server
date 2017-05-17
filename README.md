Simple File Sharing
===================

This project implements a simple PHP server for sharing files with others over a standard 
web server. Just clone this project in a folder on your Apache and you should be good to go.

Each shared file is stored inside an enclosing folder with a sufficiently long random name. 
This simple strategy ensures that shared files cannot be accessed without knowing the 
correct URL. Only brute-force guessing of the secret names would allow enumeration of all 
available downloads.

You can send people short links of the form `https://example.com/<secret>#<command>` with 
`<secret>` containing the secret folder name and the optional `<command>` part being either 
`direct`, `download`, or `view`. Invoking such a short link redirects you to the file within 
the secret folder directly or to a link with the `?download` or `?view` query string command 
(see below). If the `<command>` part is omitted, the server decides based on the file type 
whether to download or show it in the browser.

Because this is work in progress, the server so far only implements the downloading part of 
file sharing. Uploading has to be performed through administrative access to your web 
server. I use SSH for now, until I get around to implementing a nice upload interface.

This work is licensed under the [GNU AGPL v3](http://www.gnu.org/licenses/agpl-3.0.html) or 
higher.

Query String Commands
---------------------

The server works by reacting to various commands which are passed in the query string 
portion of the URL:

**`?download`**  
Add a `Content-Disposition` header so that the browser downloads the file instead of 
displaying it.

**`?gc`**  
Garbage-collects stored files. Uploads are automatically deleted after 32 days, when this 
command is regularly run by a cron job. Collection can be delayed by touching the enclosing 
folder or prevented indefinitely by changing permissions to read-only.

**`?resolve`**  
Given a secret folder name, redirect the user to the first file stored within that folder. 
This command is used to implement short URLs that omit the file name and contain the secret 
only.
