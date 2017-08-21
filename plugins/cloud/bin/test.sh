MAILADDR='butteff@yandex.ru'
paramval='cpu'
percent='20'
username='htvcenter'
MAILTEXT="Hello $username,Your budget set for the $paramval resource is reaching its limit set. You are currently reaching $percent% according to the alert settings.In order for you to manage your resources, please log into your Cloud portal to manage your existing Virtual Machines consuming the resources. Thank you"

echo "$MAILTEXT" >> /tmp/mailsender
mail -s "Fortis Resource Alert" "$MAILADDR" < /tmp/mailsender
