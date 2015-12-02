ISPConfig Postfix SPF
=========================


# REQUIREMENTS

Debian-like OS with Postfix


## NOTES


Was created for ISPConfig, but works without.


# INSTALLATION (as root)

```
git clone https://github.com/alexalouit/ISPConfig-postfix-spf.git
cd ISPConfig-postfix-spf
php -q install.php
```

# MANUAL INSTALLATION

make backup!

update package list and install python package
```
apt-get update && apt-get install postfix-policyd-spf-python
```

configure postfix
```
postconf -e 'policy-spf_time_limit = 3600s'
postconf -e "smtpd_recipient_restrictions=$(postconf -d smtpd_recipient_restrictions | cut -c32-), check_policy_service unix:private/policy-spf"
echo -e 'policy-spf  unix  -       n       n       -       -       spawn\n  user=nobody argv=/usr/sbin/policyd-spf' >> /etc/postfix/master.cf
```

reload postfix
```
postfix reload
```

A world with less spam is better world.

For better result, and prevent localtraffic openrelay (many smtp servers accept unauth mail from local address to local address).

For avoid it, set softfail as reject by default (refer as ```/usr/share/doc/postfix-policyd-spf-python/policyd-spf.conf.commented```), add ```Mail_From_reject = Softfail``` in ```/etc/postfix-policyd-spf-python/policyd-spf.conf```
```
echo "Mail_From_reject = Softfail" >> /etc/postfix-policyd-spf-python/policyd-spf.conf
```

then, you will see lines like this in /var/log/mail.info
```
Dec  2 02:21:21 mail policyd-spf[26058]: Permerror; identity=helo; client-ip=%; helo=%; envelope-from=%; receiver=% 
Dec  2 04:12:08 mail policyd-spf[31129]: Fail; identity=helo; client-ip=%; helo=%; envelope-from=%; receiver=% 
Dec  2 04:50:09 mail policyd-spf[432]: None; identity=helo; client-ip=%; helo=%; envelope-from=%; receiver=% 
Dec  2 04:50:09 mail policyd-spf[432]: Pass; identity=mailfrom; client-ip=%; helo=%; envelope-from=%; receiver=% 
Dec  2 04:53:43 mail policyd-spf[535]: Softfail; identity=helo; client-ip=%; helo=%; envelope-from=%; receiver=% 
Dec  2 04:07:53 mail policyd-spf[30835]: Neutral; identity=mailfrom; client-ip=%; helo=%; envelope-from=%; receiver=%
```
