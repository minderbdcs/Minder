select cast(dateadd(hour,10,cast('NOW' as timestamp)) as varchar(22)) from control;
  SELECT device_id,current_person,cast(dateadd(hour,10,current_logged_on) as varchar(22)) FROM SYS_EQUIP
WHERE
DEVICE_TYPE IN ('HH','PC')
AND ((COALESCE(CURRENT_PERSON,'') <> '')
OR   (COALESCE(CURRENT_LOGGED_ON,'') <> ''))
;

