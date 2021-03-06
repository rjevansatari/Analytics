drop table if exists tmp.dau;

create temporary table tmp.dau(index(user_id))
as 
select user_id, 
date(stat_date) as stat_date
from star.s_user_day
where game_id=$gameid
and client_id=$clientid
group by 1,2
;

drop table if exists tmp.installs;

create temporary table tmp.installs(index(user_id))
as 
select user_id, 
min(stat_date) as install_date
from tmp.dau
group by 1
having min(stat_date) >= $startdate
;

drop table if exists tmp.retention_summary;

create temporary table tmp.retention_summary
as
SELECT install_date,
datediff(stat_date, install_date) as ddiff, 
count(distinct i.user_id) as cnt
FROM tmp.installs i 
left join tmp.dau d 
on i.user_id  = d.user_id
GROUP BY 1,2
;

SELECT install_date, 
users,
day1 *100.0 / users as "D1%",
day2 *100.0 / users as "D2%",
day3 *100.0 / users as "D3%",
day4 *100.0 / users as "D4%",
day5 *100.0 / users as "D5%",
day6 *100.0 / users as "D6%",
day7 *100.0 / users as "D7%",
day8 *100.0 / users as "D8%",
day9 *100.0 / users as "D9%",
day10 *100.0 / users as "D10%",
day11 *100.0 / users as "D11%",
day12 *100.0 / users as "D12%",
day13 *100.0 / users as "D13%",
day14 *100.0 / users as "D14%",
day15 *100.0 / users as "D15%",
day16 *100.0 / users as "D16%",
day17 *100.0 / users as "D17%",
day18 *100.0 / users as "D18%",
day19 *100.0 / users as "D19%",
day20 *100.0 / users as "D20%",
day21 *100.0 / users as "D21%",
day22 *100.0 / users as "D22%",
day23 *100.0 / users as "D23%",
day24 *100.0 / users as "D24%",
day25 *100.0 / users as "D25%",
day26 *100.0 / users as "D26%",
day27 *100.0 / users as "D27%",
day28 *100.0 / users as "D28%",
day29 *100.0 / users as "D29%",
day30 *100.0 / users as "D30%",
day1,
day2,
day3,
day4,
day5,
day6,
day7,
day8,
day9,
day10,
day11,
day12,
day13,
day14,
day15,
day16,
day17,
day18,
day19,
day20,
day21,
day22,
day23,
day24,
day25,
day26,
day27,
day28,
day29,
day30
FROM
(
    SELECT install_date, 
    sum(case when ddiff=0 or ddiff is null then cnt else 0 end) users,
    sum(case when ddiff = 1 then cnt else 0 end) day1,
    sum(case when ddiff = 2 then cnt else 0 end) day2,
    sum(case when ddiff = 3 then cnt else 0 end) day3,
    sum(case when ddiff = 4 then cnt else 0 end) day4,
    sum(case when ddiff = 5 then cnt else 0 end) day5,
    sum(case when ddiff = 6 then cnt else 0 end) day6,
    sum(case when ddiff = 7 then cnt else 0 end) day7,
    sum(case when ddiff = 8 then cnt else 0 end) day8,
    sum(case when ddiff = 9 then cnt else 0 end) day9,
    sum(case when ddiff = 10 then cnt else 0 end) day10,
    sum(case when ddiff = 11 then cnt else 0 end) day11,
    sum(case when ddiff = 12 then cnt else 0 end) day12,
    sum(case when ddiff = 13 then cnt else 0 end) day13,
    sum(case when ddiff = 14 then cnt else 0 end) day14,
    sum(case when ddiff = 15 then cnt else 0 end) day15,
    sum(case when ddiff = 16 then cnt else 0 end) day16,
    sum(case when ddiff = 17 then cnt else 0 end) day17,
    sum(case when ddiff = 18 then cnt else 0 end) day18,
    sum(case when ddiff = 19 then cnt else 0 end) day19,
    sum(case when ddiff = 20 then cnt else 0 end) day20,
    sum(case when ddiff = 21 then cnt else 0 end) day21,
    sum(case when ddiff = 22 then cnt else 0 end) day22,
    sum(case when ddiff = 23 then cnt else 0 end) day23,
    sum(case when ddiff = 24 then cnt else 0 end) day24,
    sum(case when ddiff = 25 then cnt else 0 end) day25,
    sum(case when ddiff = 26 then cnt else 0 end) day26,
    sum(case when ddiff = 27 then cnt else 0 end) day27,
    sum(case when ddiff = 28 then cnt else 0 end) day28,
    sum(case when ddiff = 29 then cnt else 0 end) day29,
    sum(case when ddiff = 30 then cnt else 0 end) day30
    FROM 
    tmp.retention_summary
    GROUP BY install_date
) a
ORDER BY install_date desc;
