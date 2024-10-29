-- Clean up some old old events in a murky state where they had more than one subevent for the same timing
-- First we move two matches from one subevent to another where both have matches
UPDATE matches SET subevent = 11958 WHERE subevent = 7729;
-- Then we delete all the doubled up subevents that don't have any matches which puts us in the good state
DELETE FROM
    subevents
WHERE
    id IN (
        SELECT
            s.id
        FROM
            subevents AS s
        LEFT JOIN
            matches AS m ON s.id = m.subevent
        WHERE
            parent IN (SELECT parent FROM subevents GROUP BY parent, timing HAVING COUNT(*) > 1)
        GROUP BY
            s.id
        HAVING
            COUNT(m.subevent) = 0
    );
-- Then we add a constraint to prevent such a thing in future
ALTER TABLE subevents ADD CONSTRAINT unique_timing_parent UNIQUE (timing, parent);
