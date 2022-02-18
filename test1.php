<?php

				$accounts = file('start/accounts.txt');
				$messages = file('start/messages.txt');
				$times = file('start/times.txt');
				
				$rand_messages = array_rand($messages, count($accounts));
				$rand_times = array_rand($times, count($accounts));

				foreach ($accounts as $line_num => $line) {
					
					echo time()+$times[$rand_times[$line_num]]."<BR>";
					echo $messages[$rand_messages[$line_num]];
					
				
				}
					?>