Hello {{ $email_data['name'] }}
<br><br>
Welcome to Tamago!
<br>
Please click the below link to verify your email end activate your account!
<br><br>
<a href="http://localhost:3000/verify?code={{ $email_data['verification_code'] }}">Click Here!</a>

<br><br>
Thank you!
<br>
Tamago
