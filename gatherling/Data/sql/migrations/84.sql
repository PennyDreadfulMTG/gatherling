-- Get rid of The Player With No Name and all his manifestations
UPDATE events SET host = 'bakert99' WHERE host = '';
UPDATE events SET cohost = NULL WHERE cohost = '';
DELETE FROM players WHERE name= '';
