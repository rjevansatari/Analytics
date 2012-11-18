#select length(value),count(*)
#from tmp.outlaw_events_20121112
#group by 1,2
#;

select * from tmp.outlaw_events_20121112
where length(value)=80
limit 10;
