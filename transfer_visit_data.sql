-- Code to create visits table (has not been tested)
CREATE TABLE ncbirdin_a60.a60_trailmgmt_visits (
		ID mediumint(9) NOT NULL AUTO_INCREMENT,
		DTTM timestamp,
		NCBTUSERID text,
		PLATFORM varchar(100),
		BROWSER text,
		LAT decimal (11,8),
		LON decimal (11,8)
		PRIMARY KEY  (ID)
		)

-- Code to copy data from one sites table to another

INSERT INTO ncbirdin_a60.a60_trailmgmt_visits (
	DTTM,
	NCBTUSERID,
	PLATFORM,
	BROWSER,
	LAT,
	LON
)
SELECT
	DTTM,
	NCBTUSERID,
	PLATFORM,
	BROWSER,
	LAT,
	LON

	FROM ncbirdin_ncbt_data.visits;