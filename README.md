example:
```
$sanitated = new Sanitation();
$input = '`foo!@#$%^&*bar<br><p><a>"';
$input2 = 'true';
$input3 = '1234abcs';
$sanitated->sanitate('name', 'str', $input);
$sanitated->sanitate('logged', 'bool', $input2);
$sanitated->sanitate('id', 'int', $input3);
var_dump($sanitated->result);
```
