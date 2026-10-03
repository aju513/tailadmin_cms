<p>Name: {{ $details['name'] }}</p>
<p>Email: {{ $details['mail'] }}</p>
<p>Phone: {{ $details['phone'] }}</p>
<p>Country: {{ $details['country'] }}</p>
<p>{!! nl2br(e($details['message'])) !!}</p>
