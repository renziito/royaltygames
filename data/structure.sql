drop table if exists rounds;
create table rounds(
	id bigint not null primary key auto_increment,
	random decimal(18,10) null,
	angle decimal(18,10) null,
	ticket decimal(18,10) null,
	date_created datetime not null,
	status tinyint(1) not null default'1'
);
drop table if exists round_players;
create table round_players(
	id bigint not null primary key auto_increment,
	round_id bigint not null,
	user_id int not null,
	user_name varchar(45) not null,
	color varchar(11) not null,
	bet decimal(18,4) not null default'0',
	chance decimal(18,10) null default'0',
	percent_start decimal(18,10) null default'0',
	percent_end decimal(18,10) null default'0',
	date_created datetime not null,
	status tinyint(1) not null default'1'
);

drop table if exists round_winners;
create table round_winners(
	id bigint not null primary key auto_increment,
	round_id bigint not null,
	roundplayer_id bigint not null default'0',
	date_created datetime not null,
	status tinyint(1) not null default'1'
);

drop table if exists settings;
create table settings(
	id smallint not null primary key auto_increment,
	active tinyint(1) not null default'1',
	next_round_in int not null,
	game_start_in int not null,
	spinning_stop_in int not null,
	number_of_spins int not null,
	status tinyint(1) not null default'1'
);

INSERT INTO jackpot.settings (active, next_round_in, game_start_in, spinning_stop_in, number_of_spins) VALUES(1, 10, 90, 5, 4);

CREATE PROCEDURE `jackpot`.`sp_rounds_details`(
	in _current_round_id int,
	in _row_limit int
)
begin
	select
		r.random
		,r.angle
		,r.ticket
		,r.date_created as rdate_created
		,rr.*
	from (
		(
		select
			2 as tipo
			,rp.id
			,rp.round_id
			,rp.user_id
			,rp.color
			,rp.bet
			,rp.chance
			,rp.percent_start
			,rp.percent_end
			,rp.date_created
		from round_players as rp
		where rp.status = 1
		)
		union all
		(
		select
			1 as tipo
			,rp.id
			,rw.round_id
			,rp.user_id
			,rp.color
			,rp.bet
			,rp.chance
			,rp.percent_start
			,rp.percent_end
			,rw.date_created
		from round_winners as rw
		inner join round_players rp on (
			rp.id = rw.roundplayer_id
		)
		where rw.status = 1
		and rw.roundplayer_id <> 0
		)
		union all
		(
		select 
			1 as tipo
			,0 as id
			,rw2.round_id
			,null as user_id
			,null as color
			,null as bet
			,null as chance
			,null as percent_start
			,null as percent_end
			,rw2.date_created
		from round_winners rw2
		where rw2.status = 1
		and rw2.roundplayer_id = 0
		)
	) as rr 
	inner join rounds r on (
		r.id = rr.round_id
	)
	where r.id <> _current_round_id
	order by r.id desc, rr.date_created, rr.tipo
	limit 0, _row_limit;
END;

