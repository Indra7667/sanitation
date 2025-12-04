example:
```
$input = '`foo!@#$%^&*bar<br><p><a>"';
$input2 = 'true';
$input3 = '1234abcs';

$sanitated = new Sanitation();
$sanitated->sanitate('name', 'str', $input);
$sanitated->sanitate('logged', 'bool', $input2);
$sanitated->sanitate('id', 'int', $input3);
var_dump($sanitated->result);
```

do note that you still need to:
1. separate the data from the query (check PDO or MYSQLi)
2. avoid adding variable into raw query
\
\
try https://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
