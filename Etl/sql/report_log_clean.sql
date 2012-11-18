# Remove any unfinished reports
delete from reporting.report_log
where date(report_startts) < date_sub(now(), interval 1 day)
and report_endts is null
and report_code = 4
;

# Remove any reports older than 7 days
delete from reporting.report_log
where date(report_startts) < date_sub(now(), interval 7 day)
;
