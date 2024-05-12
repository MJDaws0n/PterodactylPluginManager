Run 
```
sudo visudo
```

Then add this line
```
www-data        ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t
```