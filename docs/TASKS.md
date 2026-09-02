# Tasks

Generated from `docs/braindump.txt` by `bin/import-tasks`. Do not hand-edit the task
lines - edit the braindump and re-run. IDs are a hash of the text, so they survive
re-ordering and re-generation.

Task text is verbatim, typos and all. Some lines hold more than one idea; split them
in the braindump if that gets in the way. Themes are assigned by keyword scoring and
a fair number will be arguable - the ID is the part that matters, not the bucket.

See `docs/WORKFLOW.md` for how to pick one up.

## At a glance

581 tasks, 5 of them already done by the foundation work.

| theme | count | what it covers |
| --- | ---: | --- |
| `bug` | 31 | confirmed defects and data integrity |
| `perf` | 14 | slow pages and N+1 queries |
| `errors` | 45 | validation, exceptions, HTTP status codes |
| `db` | 81 | schema, foreign keys, the query layer |
| `types` | 45 | types, nullability, enums, static analysis |
| `test` | 25 | test coverage and test infrastructure |
| `views` | 78 | components and templates |
| `ui` | 50 | design, layout, copy |
| `infra` | 38 | CI, tooling, logging, config, deploys |
| `arch` | 174 | structural refactors, naming, organisation |

Risk: 16 low, 542 medium, 23 high. Low risk means mechanical and easy to verify - good first tasks. High risk means it changes behaviour or touches the schema, so get tests in place first.

## Start here

Hand-picked. The first group is losing or corrupting real data in production. The
second group unblocks the rest of the plan, because until pages can be called from
a test there is no safe way to do the other 500.

- [ ] `T-5155AD` (bug/high) Calculating standings should be a transaction – it shouldn't be possible to get part-way through, get a db failure, and end up with bad standings - this happened to premodern league see chat with iron_lungs
- [ ] `T-9789CF` (bug/high) Recalculating ratings doesn't work (actually damages the db) and Show history for ignores your input and shows you composite
- [ ] `T-BA46BE` (bug/medium) Although the UI works, you actually CAN delete a player with matches recorded (say if you had a stale page open) so we should probably defned against taht as presumably it leaves things in an inconsistent state
- [ ] `T-F52FA4` (bug/medium) You get a 500 if you have two of the same entry in your maindeck – 2 Mossfire Valley 2 Mossfire Valley – we should either combine or warn and combine or just reject not 500 error
- [ ] `T-2783CC` (arch/medium) we need to make the invalid palyer has a match thing impossible at the db level even though we cleared up the cause. Player A cannot play Player B in Event C if there isnt' an entry for A-C and A-B.
- [ ] `T-2828A7` (arch/high) Can't rename admin to Admin because of web addresses so we need to move to an index.php that does the routing before we do that
- [ ] `T-291CD3` (test/medium) We need some shared way to make players, events, series, etc. simply in tests
- [ ] `T-40E262` (infra/medium) Check for reoccurrence – github.com/PennyDreadfulMTG/gatherling/issues/908; Cannot get event for deck without event ID all over the logs, for lots of decks maybe, or more than one at least; this might be one or two bugs but – Trigger now in place to prevent this check logs for prevent_null_deck/"Attempt to set entries.deck to NULL". Figure out how decks get orphaned and stop it happening. This phenomenon has happened nine times since the backup was restored, so it's not just historical cruft. Maybe we should make it impossible – SELECT * FROM decks WHERE id NOT IN (SELECT deck FROM entries WHERE deck IS NOT NULL) – I no longer remember why I was checking for this unfortunately. Try and repro on local. Try and create a test that repros. Why didn't the trigger OR the canDelete stuff help? … Same issue is last time. Made League 16.05 inactive to figure out the playoffs. when I made it active, it dropped 3 players: SN4KE, Molorri, and W1LL. I've re-registered them and will add place holder decklists for now
- [ ] `T-573C98` (arch/medium) shows everyone as seed 127 which is presumably not intentional. this seems to have been the case for years though. it's finalized events where the main rounds are single elim
- [ ] `T-664EC0` (arch/medium) We allow trailing spaces in player username and it fucks things up see Discord and also trailing-spaces.txt
- [ ] `T-6BADD6` (test/medium) Must find a way to test api, create_pairing in particular
- [ ] `T-781248` (errors/medium) Get rid of all exits in favor of exceptions
- [ ] `T-7C8D27` (errors/medium) some kind of top level error handling and logging
- [ ] `T-9BB2AE` (errors/medium) remove all calls to exit in favor of exception throwing (caught at top level)
- [ ] `T-ACDF58` (views/medium) It must be an errorr to provide or lookup a value ttha tisn't on the component/ in the context (in mustache rendering)
- [ ] `T-C35471` (test/medium) Tests that find things like type errors just loading a page - we should not be able to ship in that state
- [ ] `T-C8F056` (test/medium) test a full page rendering with Html to find things that should be in the finished page
- [ ] `T-EB9B7D` (test/medium) Some tests of event so that it doesn't break all the time - we can set up the superglobals and call main?

## Already done

Closed by the foundation work. Listed so the count stays honest.

- [x] `T-7F07F4` Figure out why PHP Warnings don't cause test failures
  - Sentry's error handler was swallowing them; now skipped under test
- [x] `T-B3C3A9` PHP Warning should end a test. You can try and read an array key that doesn't exist on a dict as a repro case
  - failOnWarning/failOnNotice enabled in phpunit.xml
- [x] `T-D445A3` Successful tests now emit a bit of logging which is not what we want. I am guessing that the success notification the subscriber (LogTestListener) happens before the finished notification to the other subscriber (DatabaseTestListener). Maybe we could make them both listen for finish and list them LogTestListener second in phpunit.xml and that way get the order we want?
  - resolved by the phpunit.xml rewrite
- [x] `T-E627F3` composer test shold proxy along args
  - composer tests -- --filter X works
- [x] `T-E782DF` must run phpstan and psalm on ci/ci
  - CI now runs phpstan, psalm, phpcs, stylelint and the ratchet

## All tasks by theme

### bug (31) - confirmed defects and data integrity

- [ ] `T-0B42EB` (medium) Kaladesh is now Standard Legal - wut – I think we assume anything in the whatsinsstandard API is standard-legal even if it's passed?
- [ ] `T-142DF4` (medium) Creating events across daylight savings makes them appear to be wrong by an hour
- [ ] `T-1652D8` (medium) consider setting a filename for calendar.php like we do for text file download - does it break anything? right now it downloads as caledanr.php which is silly
- [ ] `T-1A1E05` (medium) break apart activeEVentsAndMatches by removing the league matches part and just doing that somewhere else in a separate loop it's totally unnecessary coupling and confusing with tournament matches being extracted elsewhere
- [ ] `T-2D6ABC` (medium) Assign Medals (from reg) doesn't work but it didn't work on prod either - simplify it away?
- [ ] `T-32B382` (medium) The API will pass a string of discord, gatherling as game name (fine, if weird) but also 'mtga' and others which GameName does not support. Did I break this, or did it never work? Pretty sure it never worked but we could clean this up
- [ ] `T-32E3BE` (medium) instead of forming strings of the placeholds in the caller insetead support some nice interface on DB that lets you pass in arrays?
- [ ] `T-4AF56F` (medium) I think I removed the ability to set a Discord room? Or we never added it? Needs investigation. seriescp just says MTGO room
- [ ] `T-6813BE` (medium) I wonder if something i did garbled this message (got to be the greater than symbols) – Hello CPL players! The current Premodern League is "Premodern Monthly League 13.6". The event will run from 09/02 to 09/15. You can register for this league by going to Gatherling.com > Player CP > Active Events > Join League
- [ ] `T-69ACD5` (medium) If you try to add a match from event.php view=match and you have forgotten to put a value in newmatchround we 500 - we need to decide what we are going to do about validation in general and do whatever that is here and for 100 other possible failures
- [ ] `T-6E1796` (medium) Deck->delete should probably throw if you try to delete a deck with matches not silently do nothing
- [ ] `T-846D7F` (medium) When you ban all tribes you get dumped on legal/restricted/banned instead of back on tribes - formatcp - ps sure this is an old bug
- [ ] `T-8DD23A` (medium) Ical expects events with timestamps, which is wrong should be DateTimes, and that isn't documented anywhere
- [ ] `T-984294` (medium) miss_input was incorrectly given the bye in R3 of Penny Dreadful Saturdays 36.06
- [ ] `T-9F89B2` (medium) Reset password lets you say blank string but then things get screwy because you can't login with it
- [ ] `T-AB4A83` (medium) we use POST for a lot of things that should be in the query string or part of the url and it breaks boomarking and the back button
- [x] `T-B3C3A9` (done) PHP Warning should end a test. You can try and read an array key that doesn't exist on a dict as a repro case
- [ ] `T-BA46BE` (medium) **[start here]** Although the UI works, you actually CAN delete a player with matches recorded (say if you had a stale page open) so we should probably defned against taht as presumably it leaves things in an inconsistent state
- [ ] `T-BDEFC2` (medium) cardscp is broken, but also not linked anywhere, so either reomve it or fix it
- [ ] `T-CD296D` (medium) The way we do initial_byes will break if there's ever a space in a username
- [ ] `T-D74B56` (medium) www.gatherling.com is not using the same cookie as gatherling.com which is very stupid – have www redirect to same page on gatherling.com instead of being a dupe
- [ ] `T-D97701` (medium) Click "Crate Next Event" or "Crete Next Season" then use one of the menu items like Event Settings - breaks - should hide them if they arent' clickable, true on old gatherling too
- [ ] `T-E1067D` (medium) Why does loading "add cardset" in admincp take a long time??
- [ ] `T-E515A1` (medium) AllRatings doesn't find my ratings from before season 7 for the most part - why is that?
- [ ] `T-EE2D9F` (medium) It is wrong that NotFoundException and NotFoundInDatabaseException are essentially the same thing it's stupid
- [ ] `T-EE7477` (medium) the DS and other two letter prefixes are dumb, and also break our naming convention where it should be Ds
- [ ] `T-F11AB9` (medium) events with long names like the kick off and the 500 mess up the headers of each column in seriesreport.php because they can't be shortened to "36.02" and similar. See link. Wer'e doing this which is cute but doesn't work for the 500 or the kick off or Super Saturday – shortName = preg_replace("/^{ series->name} /", '', $eventName);
- [ ] `T-F49ECE` (medium) Gatherling mouseover dfcs doesn't work because of that super long name treatment
- [ ] `T-F52FA4` (medium) **[start here]** You get a 500 if you have two of the same entry in your maindeck – 2 Mossfire Valley 2 Mossfire Valley – we should either combine or warn and combine or just reject not 500 error
- [ ] `T-5155AD` (high) **[start here]** Calculating standings should be a transaction – it shouldn't be possible to get part-way through, get a db failure, and end up with bad standings - this happened to premodern league see chat with iron_lungs
- [ ] `T-9789CF` (high) **[start here]** Recalculating ratings doesn't work (actually damages the db) and Show history for ignores your input and shows you composite

### perf (14) - slow pages and N+1 queries

- [ ] `T-20636C` (medium) there is also a count query in an inner loop in SetList
- [ ] `T-4962C2` (medium) Deck constructor does a lot of queries can probably be made more efficient if it is at all slow
- [ ] `T-5A7430` (medium) although i improved it considerably the query-in-loop of getFinalResults should just be one query - provide a facitliyt for that type of query in DB probably
- [ ] `T-60916E` (medium) findIdenticalDecksInternal could eventually be ridic performing bad, may already be, loads each possible matching deck identically could be one query to find the ones with palyernames if nothing else maybe better than that
- [ ] `T-6E79B3` (medium) The way we get record string in deck search is multilevel nonsense with multiple queries per deck and just makes no sense at all
- [ ] `T-759A66` (medium) loading player in a loop in ratingsTable kinda sucks maybe
- [ ] `T-83C388` (medium) allmatches is really slow and does indeed load every match you ever played
- [ ] `T-869C99` (medium) match listing is kinda slow on the big premodern events – almost all the time spent here is spent in playerDropped doing new Entry which loads both Event (always the same) and the entry itself (which does at least differ). If we load the Event only once and all Entries in one query this would make an 80 person event load in half the time or less.
- [ ] `T-9BB8FD` (medium) Speed as a feature – log slow pages like we log slow queries. With register_shutdown probs?
- [ ] `T-A8E14F` (medium) updateDecksFormat could just be one UPDATE not a SELECT and then a zillion UPDATEs
- [ ] `T-BFBC06` (medium) decksearch query is not actuallyslow it's some giant loop that does zillions of queries
- [ ] `T-C92315` (medium) we do a count query inside a loop for every card in EditSet that can be pushed to the outer query with a join for presumably a big speedup
- [ ] `T-C9ED19` (medium) Creating an Event model from the Dto's name in Eventlist when looping over events is (a) horribly inefficient doing 100 queries and (b) shows how Dto and Model need to be unified so our query could return 100 events and it make it unnecessary to do even one more query
- [ ] `T-FC9D3F` (medium) eventreport.php's list view just maxes out at 100 and there's no pagination

### errors (45) - validation, exceptions, HTTP status codes

- [ ] `T-D80471` (low) Combine the messages with the Registraiton::ERROR constants maybe by doing excpetion throwing?
- [ ] `T-DCCC43` (low) if (eventName null && Event::exists( eventName)) { at teh top of event report means a typo in url takes you list view instead of 404
- [ ] `T-004F41` (medium) We have to do proper validation of event creation and by extension everything
- [ ] `T-025170` (medium) the catch in newEventFromEventName is wildly risky and completely the wrong way to do it - throw a hyper specific exxception and catch that by name or something else
- [ ] `T-028E81` (medium) maybe need to use LineFormatter to log exceptions with includeStackTraces
- [ ] `T-03B525` (medium) adminControlPanel uses the error class to display just a normal result of somethign going well
- [ ] `T-03B68E` (medium) Although I have downgraded the error message to a warning we still see loads of apostrophe stuff in the logs and it deserves a proper investigation; Trailing apostrophe on URL causes 500 when it's really a 400 (and it'd be nice to know where it came from) /seriesreport.php?series=Momir%20Basic&season=1'
- [ ] `T-068EF1` (medium) Creating an event needs so much validation that it doesn't have! but for now we can catch any DatabaseException and present a message and the form again at least
- [ ] `T-072AE3` (medium) The default view of report.php should be your pending match, not an error or a blank screeen then we can link it in bot command (I changed it to redirect to player.php but this idea is better)
- [ ] `T-0A4CF3` (medium) the whole errormsg append but also return pattern is daft in event/host cp
- [ ] `T-0D84FA` (medium) returning false instead of throwing from getPlayerWins and getPlayerLosses is probably very silly but I don't know what it'd break to remove it
- [ ] `T-1D9528` (medium) seriescp - it's my theory than the formatErrors that DONT pass a view actually error out if you hit continue? or something. maybe we're always meant to send you to settings from Continue? Rip out the continue behavior entirely?
- [ ] `T-22519A` (medium) In Deck->delete we set errors to an empty list even if there are matches and we otherwise refuse to delete. I think an exception for having matches is more appropriate and the current behavior is weird but i'm not brave enough to change it rn
- [ ] `T-2AC08B` (medium) NotFoundException needs to be reviewed now that it has a user-facing error message and is a BadRequestException
- [ ] `T-3914E9` (medium) If a param starts with a colon either clip it or raise exception in db code don't treat it as sensible input
- [ ] `T-3F9870` (medium) We should for sure be sending 403 forbidden somtimes
- [ ] `T-453EAE` (medium) We should BadRequest not 500 when Request doesn't like the input, but log probably
- [ ] `T-464250` (medium) points adjustment turns gibberish into 0 in first box instead of rejcting/validating
- [ ] `T-4BBC74` (medium) also store user-agent with errors so we know if a real human was affected, maybe session usenrma
- [ ] `T-605C15` (medium) It's dangerous to hvae the exception message and the user message be thes ame iin ValidationException, we can do better when we have full validation. We're showing stack traces in user-visible error messages as it stands becasue ValidationException is a subclass of BadRequestException but it's getUserErrorMessage func just proxies to getMessage; ValidationException getUserMessage must either (a) be the exact same as RequestException and share the code/make them one thing, or (b) be just as sophisticated at telling folks "you can't add an event with series=Monkey because there's no such series as Monkey"
- [ ] `T-6B901E` (medium) make consistent when we raise and when we return in the various updates in Formats.php
- [ ] `T-73BE69` (medium) html validation
- [ ] `T-741259` (medium) can we make commit and rollback into shared code? they are very similar but subtly different because we rollback in case of error but do not commit
- [ ] `T-781248` (medium) **[start here]** Get rid of all exits in favor of exceptions
- [ ] `T-79605C` (medium) gatherling's 404 page sucks
- [ ] `T-7C8D27` (medium) **[start here]** some kind of top level error handling and logging
- [ ] `T-8441F9` (medium) refactor Deck->save so it's not just one giant three hundred+ line nonsense, which might allow us to more sensibly wrap in a try…catch for DatabaseException and then explciitly rollback in the catch which we aren't doing currently
- [ ] `T-86F975` (medium) When in commandline context like running db-upgrade dont' give an html page as an error – also don't hide stack trace bcause you can't tell i'm not an admin
- [ ] `T-90C9C1` (medium) it would be nice to selectOnly instead of selectOnlyOrNull and thus fail noisily in Matchup constructor but I don't quite dare at present
- [ ] `T-9B04FA` (medium) Player::createByName and by extension Player::findOrCreateByName assume way too much about the success fo the operation - it can and should fail if (for example) you supply a username with mor than 40 chars - add exception throwing and exception handlings and generally make registraiton better
- [ ] `T-9BB2AE` (medium) **[start here]** remove all calls to exit in favor of exception throwing (caught at top level)
- [ ] `T-AE0E7D` (medium) All these InvalidArgumentExceptions would be way way better as not-nullable properties; Some of our uses of InvalidArgumentException as well as being not-a-GatherlingException are also just semantically wrong; the InvalidArgumentException in DiscordAuth sucks - come up with a plan; replace invalidargumentexception with a more specific gatherlig exception evwyerhe; I used an InvalidArgumentException in getEventStandings but that isn't one of ours it's a built-in. is that ok?
- [ ] `T-B35C86` (medium) in deckdl.php it'd be nicer to 403 than redirect to playercp for a permissions err? And log?
- [ ] `T-B4C104` (medium) the way deckXmlParser errors out (and everything in the entire app really) is wrong. need to send a 400 if you gave us rubbish
- [ ] `T-B75E2F` (medium) If you try to sign up with a username that is too long the INSERT fails silently then the SELECT to find the user fails with a RuntimeException. Instead we should have full on proper validation. See signup-too-long.txt
- [ ] `T-BDC4D9` (medium) Insufficient Permissions is quite a generla name but the error message mentiones format specifically as it is only for formatcp as it currently stands
- [ ] `T-C436A7` (medium) The validation in Event is dead simple, fails one error at a time, doesn't check things like metaurl is empty string or a url, season is positive int, etc. And would be repeated in too many places if we added it to all models. So make a validation library that does inptu validation and model validation and everything but at least we have a stake in the ground. Forms with red squares around all the invalid inputs to follow in some happy future
- [ ] `T-D873D1` (medium) Do I need a try catch around Deck->delete to explicitly rollback or is default behavior ok?
- [ ] `T-E14A46` (medium) insertcardset kinda explodes if you give it a bad file insted of giving a nice error message
- [ ] `T-ECE341` (medium) SetMissingException is probably either a NotFoundException or a subclass of NotFoundException but let's make sure that's the bubble-up behavior we want/that the 404 page displays the message
- [ ] `T-F1D926` (medium) A series not being found in seriescp (mangled querystring for example) should be a 400 not a 500 – but in general we need to "solve" validation
- [ ] `T-F46A91` (medium) when a test fails we sometimes get the ROLLBACK but nothing before that
- [ ] `T-1FC41B` (high) series_seasons.format can nearly be an FK with a couple exceptions
- [ ] `T-9AEE12` (high) the operations in formatcp should be transactions and shuold all succeed or all fail not return in the middle of inserting things
- [ ] `T-F42246` (high) Some things in handleActions short circuit when they encounter an error and some accumulate a list of errors – we should probably be consistent and use a transaction

### db (81) - schema, foreign keys, the query layer

- [ ] `T-3C54AC` (low) Defend against empty string as a playera or playerb in matches but probably by doing the right thing not by hardcoding a check for empty string but maybe we have to just delete player1 with name empty string?
- [ ] `T-7BFE5F` (low) Condense/canonicalize whitespace when logging sql
- [ ] `T-016517` (medium) should value call values instead of being separate? should they both call select?
- [ ] `T-01DEE6` (medium) scryfallId column in cards is named weirdly (camelCase not snake_case)
- [ ] `T-026902` (medium) in a sense an option is an object as is a select
- [ ] `T-03CF46` (medium) DatabaseException should be more structured accepting a sql param, a params param, etc. instead of the murky json_encode string stuff
- [ ] `T-06566F` (medium) Should it be a fatal error to provide a param that is unused in db code?
- [ ] `T-07F74A` (medium) We should find a way to always bindParams that isn't calling it in each func in Db
- [ ] `T-082F05` (medium) start and many other things in the db as DATETIME should be DateTime in the PHP not string
- [ ] `T-0A7C22` (medium) modify_set can only delete entries you can't actually modify the set although the UI implies otherwise
- [ ] `T-12C20D` (medium) if you re-run insertcardset it just addes yet more copies to the db???? or misreports number inserted
- [ ] `T-15D011` (medium) The way our db code works we're not able to tell the difference between an int and a string of an int in values or value. We can in fetch_class stuff but where we're not using that it comes out of the db stringish always which means we have to be lax about what we accept as an int all the way through all our stuff. but querystrings work the same way so i think it works out ok?
- [ ] `T-16075C` (medium) SELECT player FROM standings WHERE event = ? AND active = 1 ORDER BY seed is not a stable ordering beasue lots of people are seed 127
- [ ] `T-161CB3` (medium) Everything about a Dto is readonly after db code is done with it
- [ ] `T-177CC2` (medium) SELECT icon.png info.plist rtm_icon.png rtm_icon_1.png rtm_icon_2.png rtm_icon_3.png FROM season_points WHERE event IS NULL OR event NOT IN (SELECT name FROM events);
- [ ] `T-19FF1D` (medium) Just do one insert into deck errors in Deck.php->Save
- [ ] `T-249C03` (medium) Active Events and Active Matches being below Upcoming Events on Player CP in single column view is bad – this page needs a rethink and to become hierarchical. If you have a pairing in an active tournament that's way more important than that there's a tournament you could join in 11 days time
- [ ] `T-2AFEFB` (medium) values should use select so that all selects flow through the same code/get the same bindParams behavior/etc. and cannot get out of sync, buuut then it can't use fetchColumn? Does that matter? Square the circle
- [ ] `T-305031` (medium) fractional timezones don't get saved properly and 10 people have a timezone of 123 in the db
- [ ] `T-440A3E` (medium) Database isn't really a model, some of the stuff in models are really helpers
- [ ] `T-4EC86E` (medium) There are a lot of cardsets in the db with a code of NULL. Update them and then don't allow that and then remove the IS NOT NULL in SetScraper
- [ ] `T-5004ED` (medium) update to latest stable php, update phpunit
- [ ] `T-5134B5` (medium) cardscp.deletecards could issue just one query if we expanded DB to cope with lists
- [ ] `T-514A51` (medium) lint rule to disallow question marks in sql and demand colon-prefixed named params
- [ ] `T-52B7AD` (medium) do we want to genralize opts in AllMatchForm for the whole app for when we want to stuff the same value into value and text for a select option?
- [ ] `T-579F73` (medium) The numebrs in something like tinyint(2) are misdleading and stupid. Redefine every int in the db to just be base type and not have a "diplay width" to avoid confusion.
- [ ] `T-5B0D57` (medium) there should be a requires-sql-and-params subclass of DatabaseException called something like QueryException
- [ ] `T-68A44A` (medium) Better support league result confirmation – "If there are other matches earlier than yours in the queue that haven't been confirmed, Gatherling won't update. Mods do a manual sweep a few times each day"
- [ ] `T-6B8B7C` (medium) remove the ignoreErrors in phpstan in favor of replacing mysqli stuff with DB stuff
- [ ] `T-6EF552` (medium) there are two almost identical if branches in formatcp for 'Update Cardsets'
- [ ] `T-703AB1` (medium) probably move the dto into a subdir
- [ ] `T-70F8A4` (medium) Seven decks in the db are marked as tribe — which makes no sense
- [ ] `T-712F46` (medium) formatRenameForm is so much like the delete form and so on
- [ ] `T-7A58A3` (medium) Tiebreakers investigation – did anything change, was it broken already, can we do better for people with only byes (perfect tiebreaks) and people with no matches only/any missed rounds due to late join or drop (worst tiebreaks). Add tests
- [ ] `T-7B5D68` (medium) subtype_bans and tribe_bans – clean up format column so it can be made a proper foreign key
- [ ] `T-7F419B` (medium) our php.yml doesn't mention pdo?
- [ ] `T-7FE545` (medium) Some parts of handleActions like the insert in updateCardSets and some of the stuff that is inline and doesn't have its own func just doj't check for success and have no chance to return an eerror
- [ ] `T-826620` (medium) SELECT tribe fROM decks WHERE tribe NOT IN (SELECT name fROM tribes);
- [ ] `T-8871A2` (medium) you shouldn't be able to delete a format without confirming it will blow away tens of thousands of matches
- [ ] `T-8990CC` (medium) There are a bunch of waitfor delay usernames in the players table; there is a lot of crap in the players table - sql injection attempts and such - clean it up?
- [ ] `T-947B99` (medium) we slice ratingsTable in memory not in the db query which is nonsense
- [ ] `T-948BCD` (medium) We should support IN in SELECTs passing in an array for the param in our db code either via PDO methodss or doing it ourselves in one place for all time
- [ ] `T-9BE461` (medium) you should be able to reg for a league from the event page even after it starts, you should be able to late join a tournament from there too
- [ ] `T-A16BBF` (medium) the whole of metastats can probably be done more simply in the sql
- [ ] `T-A60297` (medium) Invent a way to do IN queries that uses placeholders but isn't a pain in the ass in teh caller only in Db and using it in insertIntoLegalList
- [ ] `T-A6B1B7` (medium) document sql river style in README or a CONTRIBUTING
- [ ] `T-A97E0A` (medium) there are a few EXISTS queries in the codebase that go by other forms like count on strings see isOrganizer
- [ ] `T-AB62A2` (medium) values is more frequently called "column"?
- [ ] `T-B692E0` (medium) allratings isn't working entirely well - when you select a format and hit Show History it doesn't select that format in the drop down
- [ ] `T-BD214F` (medium) Oh, I see the issue a bit more clearly now that you pointed that out. It's because you have 1 Blue Elemental Blast in your sideboard but also 1 blue elemental blast a bit further down. I will update the code to do something more sane than exploding in an error in such a case. But for now you can "Create Deck" and enter a list with only one entry for BEB and it should behave 🙂 Thanks for reporting.
- [ ] `T-C32166` (medium) I wonder if we should make it impossible to delete an entry that has matches to avoid this situation. I kind of thought we already have that. But maybe there's a chink in the armor somewhere.
- [ ] `T-C3277F` (medium) seriesreport behaves poorly for series that have no entries in series_seasons you need at least one in an earlier season to find the points -- if you try to look at (say) PD FNM Season 34 it shiould say "admin needs to set points before this is meaningful" ratehr than show nothing OR we could default to some sensible points instead of all zeroes in getSeasonRules, it's getSeasonRules where we'd nee dto detect the "missing points being set" case
- [ ] `T-C64C1B` (medium) You can break a format just by adding every block, core and exgtra set - exceeds the column lenght (in formatcp)
- [ ] `T-C667B1` (medium) In general when you do something to an event like, for example, add two new subevents via newSubevent in Event->save (when the event is new), you should update the object we have in memory to reflect that (so in this case setting maindid and finalid properties) rather than requireing a realod from the db - fix this one exmaple, look for others?
- [ ] `T-C6A01E` (medium) Remove id from bans tables
- [ ] `T-C901EF` (medium) We are not strongly typing the sql input params
- [ ] `T-C95EAF` (medium) Why does profile use GET to update email address etc?
- [ ] `T-CC36F9` (medium) if the DatabaseTestListneer fails that's a catastropic failure not a Wanring
- [ ] `T-CDB372` (medium) insted of reaching out to global config in DB::conntect do it in some di-enabled config class and it's already handled by the time we get thre
- [ ] `T-D69061` (medium) series and season in season_points are implied by event and can be gotten by join, remove those columsn
- [ ] `T-DD7F39` (medium) the existence of EventDto as a copy of all the properties of Event is kind of an abojmination and we should merge them somehow without breaking our db fetch class stuff
- [ ] `T-DD87E2` (medium) treating stuff like created_date/registered_at as a string with things like DB::optionalString sucks - make dates a first order type
- [ ] `T-DDA4A6` (medium) Would Standings->save be better modeled as an INSERT INTO IF THEN UPDATE thingy?
- [ ] `T-E3F8DC` (medium) SELECT FROM entries WHERE deck IS NULL - these are people who click to late reg but do NOT late reg. They don't get removed at event start (because that's in the past) and they remain hanging around with no related deck forever
- [ ] `T-ED885D` (medium) DB::value doens't complain if more than one row is returned but should BUT that will break it's use in currentThrough so be carefeul
- [ ] `T-F64A8C` (medium) Change sql style to river style throughout
- [ ] `T-F6B8F1` (medium) it's pretty crazy that we create a player in the db even if you send us garbage in register
- [ ] `T-FB1346` (medium) CardSet::insert should be more/less sophisticated and not take an arbitrary string but rather know what it's getting and know the mtgjson url stuff itself etc.
- [ ] `T-FB7AEF` (medium) Maybe log all sql?
- [ ] `T-FFDA5D` (medium) DB::select should explode if you pass the wrong Dto not just work silently like stdClass
- [ ] `T-031D8B` (high) The comment in Setup.php says to dump format info for STandard/Modern/Legacy but is that necessary if startnig from scratch (in docker for example?) or would the get created by updateDefaultFromats? Should we make updateDefaultFormats do this rather than requiring it in schema.sql?
- [ ] `T-4C19E4` (high) all the casting to bool in EventForm is ugly but maybe necexsay without serious migration
- [ ] `T-576844` (high) the SCHEMA lives under the WEB ROOT - this is dumb
- [ ] `T-71F319` (high) make event.series (and maybe many other things) not nullable in a migration and change how they are represented as properties in code
- [ ] `T-83482E` (high) card_name is NOT an FK on bans (and other related tables) and is a form of duplicate record keeping probably because a previous author didn't understand joins? Add FKs, remove it, do better generally etc.
- [ ] `T-90E3F5` (high) Re-checkpoint the database to run less migrations on test runs. Did it make composer test faster? If so, schedule to do this regularly.
- [ ] `T-9F007D` (high) There are a bunch of formats in restrictedtotribe that are not in formats; also subtype_bans - clean them up and make a proper FK relationship for every format column in the db. Maybe switch to id not name as the key
- [ ] `T-A64DC5` (high) SELECT series_name FROM formats WHERE series_name NOT IN (SELECT name FROM series) yields many entries for "System" what is the purpose of that column? Can we make it an FK?
- [ ] `T-A98D92` (high) Format->delete should be modernized sql and use a transaction
- [ ] `T-AA7B27` (high) using strings for pks and fks succccks and in ui to refer to events omg
- [ ] `T-E73349` (high) Even if we don't change to numeric ids and FKs we should for sure change decks.playername to player same as it is elsewhere

### types (45) - types, nullability, enums, static analysis

- [ ] `T-8AF2D8` (low) that the mtgo, mtga and paper consts live on GameName is really stupid btu taht's the only placed they are referenced currently - probably make a backed enum called Platform and then use it everywhere instead of ints
- [ ] `T-1BDEF2` (medium) Deck->name should not be nullable
- [ ] `T-2ABA1F` (medium) all component properties and just a whole hell of a lot should be readonly
- [ ] `T-3CF3B0` (medium) We shouldn't really be doing falsy checking - === null better - but I did it in a bunch of places before I decided that
- [ ] `T-43B741` (medium) it's weird that medal defaults to "dot" and is not nullable – before the tournament ends null would make sense
- [ ] `T-49BA34` (medium) Should any DateTime be DateTimeImmutable? Probs
- [ ] `T-49FB71` (medium) We need to disallow empty username and password as well as null in login, also reg after checking db??
- [ ] `T-4B8869` (medium) subevnum in getRoundmatches is a magic number for "timing" create constrants/enum
- [ ] `T-4E1295` (medium) have to declare war on mixed at some point
- [ ] `T-51FB00` (medium) Can always go further with psalm
- [ ] `T-58CCF6` (medium) cohost on createEvent must be nullable as it's nullable in the db, the whole signature of that method (All strings) is garbage.
- [ ] `T-5D1BB0` (medium) it would be nice if the partial receivers were strongly typed
- [ ] `T-634CF2` (medium) Maybe remove Blossom from subevent type in db or is it nice to know where we switched over algo?
- [ ] `T-68AEFB` (medium) should dto attrs be readonly? other things? final?
- [ ] `T-6A7A07` (medium) medal on deck should be enum not string
- [ ] `T-6CCFAE` (medium) Why does psalm the plugin give so much more useful info than psalm at the commandline?
- [ ] `T-72A68C` (medium) all these arrsasy for form elems like checkbox could be strongly typed instead, sweet!
- [ ] `T-735E87` (medium) The passing of new DateTimeImmutable() is only to get relative time which the js then overwrites. Is it an unnecessary complication? Can we use it in some way?
- [ ] `T-760D6E` (medium) We make a LOT of properties nullable because of how they are initialized it would be better to make them not nullabel and error out if load fails (Event, elsewhere)
- [ ] `T-794604` (medium) are the args to add_player_to_event really nullable? you did that for safety
- [ ] `T-80ED03` (medium) We should be tracking latest version of everything (latest stable) and that should apply to psalm which is on 6.5 not 6.9.3 for some reason
- [ ] `T-8B3F56` (medium) eventually we have to make the baseline errorrs for phpstan go away
- [ ] `T-8EB4BF` (medium) Enable all strictRules in phpstan config
- [ ] `T-937273` (medium) get to phpstan level 7 without baseline
- [ ] `T-9494ED` (medium) "Choose File" under Install New Cardset does not use our inputbutton class, but doing so doesn't help that much because it's a type=file input
- [ ] `T-985320` (medium) rather than using phpstan array doc comments just make those things simple objects?
- [ ] `T-9D9750` (medium) There's a stash of turning email privacy into an enum but maybe just scrap email privacy as a concept instead because that's both better and probably slightly easier
- [ ] `T-9E6583` (medium) the int|string nature of "game"/"gameName" beause ti can be from the 1,2,3 enum (mtgo, mtga, paper) or the string "gathelring" suuuucks
- [ ] `T-AC1C69` (medium) the default of initial_seed being 127 and it not being nullable is pretty dumb
- [ ] `T-B0A55E` (medium) the types for the swiss pairing stuff in Pairings is fairyland nonsense i haven't checked
- [ ] `T-B6C010` (medium) enum for medal
- [ ] `T-D16156` (medium) Make SUCCESS and the various ERROR_ codes in Registration work more like loginresult (and be an enum?)
- [ ] `T-D17D5D` (medium) "knwon formats" like Standard should maybe be an enum. or maybe not as other formats are creted on gatherling
- [ ] `T-D2E27D` (medium) The various values for struct (Swiss, Single Elim, etc.) should be an enum not a string
- [ ] `T-D85837` (medium) it'd be nice to make url or href a type
- [ ] `T-E00874` (medium) We have some nullables for "properties that only some path use" is that what we want?
- [ ] `T-E14A96` (medium) phpstan level 10
- [ ] `T-E46A07` (medium) env should be an enum and all the config stuff should be way more strict
- [x] `T-E782DF` (done) must run phpstan and psalm on ci/ci
- [ ] `T-F39D2E` (medium) because we use methods not declared on ResourceOwnerInterface i just used mixed in auth.php but should not - probably upgrading the library would help
- [ ] `T-F62805` (medium) All our db code declares params as string, mixed but we should make stronger guarantees. We actually always know the type of a param but we don't want to type it out every time I don't think?
- [ ] `T-F66E54` (medium) FormatsDropMenu formatType should be an enum not a string
- [ ] `T-F7CF62` (medium) the safe strtotime is not quite as "safe" as something like preg_replace if we pass it a null for example? Check stuff, cntraliezd date handling. Really the properties of models that are dates should be ImmutableDateTimes and then we don't have to strtotime all over the shop
- [ ] `T-FAB139` (medium) are the args to add_player_to_event AND LOTS OF OTHER THINGSIN THAT FILE really nullable? you did that for safety
- [ ] `T-FABC76` (medium) tribeban and subtype ban should be an enum or otherwise remove the need for the exception throwing in tribebandropmenu

### test (25) - test coverage and test infrastructure

- [ ] `T-12159E` (medium) tests for all components
- [ ] `T-182AE1` (medium) Combine testAssignTrophiesFromMatches with very similar test in EventTest
- [ ] `T-291CD3` (medium) **[start here]** We need some shared way to make players, events, series, etc. simply in tests
- [ ] `T-2C9D37` (medium) Add a test for 0 players to testAssignMedalsByStandings and then make the code work for 0 too, it never did before
- [ ] `T-43E223` (medium) Login->login is a mess - why are we using isset and assert?
- [ ] `T-464F37` (medium) If you change the test series name in testAssignMedalsByStandings to "Test Series" the test fails with a dupe, but that shouldn't happen? OR, if that's the series in teh test db, then why can't I just load that series and use it in the test, or not even load it just use its name in the createEvent call?
- [ ] `T-56E4AE` (medium) Should really test the infobot stuff
- [ ] `T-6BADD6` (medium) **[start here]** Must find a way to test api, create_pairing in particular
- [x] `T-7F07F4` (done) Figure out why PHP Warnings don't cause test failures
- [ ] `T-841478` (medium) a simple way to change log level at the commandline when running tests to get debug output
- [ ] `T-8D343F` (medium) DatabaseTestListener fires on any test run but should only fire if there's a DatabaseCase in the run
- [ ] `T-B5C952` (medium) the log outputs ABOVE the test info if a test fails which is a bit weird - can we do better?
- [ ] `T-C2F860` (medium) Shold probably use assertSame most everywre I am useing assertERquals
- [ ] `T-C35471` (medium) **[start here]** Tests that find things like type errors just loading a page - we should not be able to ship in that state
- [ ] `T-C51EDE` (medium) http used in Formats for scryfall, too, change to https and test
- [ ] `T-C7F854` (medium) A test for updating event settings so I can't break it again and not know
- [ ] `T-C8F056` (medium) **[start here]** test a full page rendering with Html to find things that should be in the finished page
- [ ] `T-CB9148` (medium) should all test classes be final? others?
- [x] `T-D445A3` (done) Successful tests now emit a bit of logging which is not what we want. I am guessing that the success notification the subscriber (LogTestListener) happens before the finished notification to the other subscriber (DatabaseTestListener). Maybe we could make them both listen for finish and list them LogTestListener second in phpunit.xml and that way get the order we want?
- [ ] `T-E08C5A` (medium) the test for event->name in StatsTable is one of dozens of null cheks that should be unnecessary
- [x] `T-E627F3` (done) composer test shold proxy along args
- [ ] `T-E9CB8B` (medium) What is use DatabaseTransactions in phpunit? Can it replace our DatabaseCase more simply?
- [ ] `T-EB9B7D` (medium) **[start here]** Some tests of event so that it doesn't break all the time - we can set up the superglobals and call main?
- [ ] `T-F69BB8` (medium) debug output might be too much even for a failed test - does that mean it's not at all useful? it's all the setup that we don't care baout can we constrain the buffer handler to stuff that really happens in the test some clever way by witching in setUp/tearDown between log and don't log?
- [ ] `T-FD5449` (medium) Need to figure out why you can't try to login with an unrelated user then reg the login with the regged user in the same test

### views (78) - components and templates

- [ ] `T-645534` (low) weird comment in the html with no TODO playerLink as name of compoojet and as arg sucks
- [ ] `T-DA57FC` (low) RoundDropMenu sounds like "the round you dropped" but is just a dropdown for choosing a round, perhaps it will spur us to rename all select components to SomethingSelect instead?
- [ ] `T-F52BAC` (low) medalSrc is kind of spread all over the app now and should be centralized somewhere; BestDecksTables also has inlined medal src; the medal image inlined in medalTable should really be a shared component; the way to get the mdeal image is hardcoded in MedalList and also Finalists; use medal component everywhere we use a medal
- [ ] `T-020503` (medium) It's insane that the default value for PlayerDropMenu def is a newline lol
- [ ] `T-023B42` (medium) SeriesDropMenu and SeriesDropMenuDS should probably be comined
- [ ] `T-05891D` (medium) Should we have a conventio for what the string formatted version of a date is called as a component/page property? Right now they are suffixed Date and that's a little type confusion - if everythng goes through Time maybe this goes away?
- [ ] `T-08B25D` (medium) there is an AuthFailed Page and an AuthFailed Component and neither do very much and they are not the same - figure something out
- [ ] `T-0F2241` (medium) NumDropMenu will select the 0 option and NOT the default option if you pass it null or empty string, but it's not as simple as changing to triple equals in the test
- [ ] `T-0F9624` (medium) seriesreport has "All" in the dropdown for Series but what does that mean? You can't see a leaderboard for more than one series at once can you?
- [ ] `T-145E80` (medium) maybe we inline the template IN the component as a heredoc kinda string?? no real reason for them to be separate?
- [ ] `T-15FF93` (medium) There are still some raw echoes in the codebase. Some very small pages that are not Pages and also the output during recalc ratings that prints "above" the page foolishly.
- [ ] `T-18BD5D` (medium) selectInput and textInput are bad names for partials/funcs that contain a tr
- [ ] `T-1C3B47` (medium) playerDropMenuArgs and roundDropMenuArgs should be components
- [ ] `T-1EBB8B` (medium) DropMenu takes args from subclass in constructor but Page expects you to set title as a property - be consistent
- [ ] `T-3167DE` (medium) there some confusion about wheter strings holding links that may or may not be present should be null or empty string when not going ot be used in components and pages. Make a definitive standard (probably empty string) and make everything conform
- [ ] `T-356B7C` (medium) in incognito mode i don't see discord login on gatherling login page - why?
- [ ] `T-3C41EB` (medium) manasrc should probably be a component
- [ ] `T-401F2E` (medium) Can we leverage mustache helpers to get the behavior we want from nested components?
- [ ] `T-4051C4` (medium) component properties can be protected or private if the rendering is moved into component
- [ ] `T-423E9B` (medium) Deal with PHP Deprecated: Mustache_Engine::loadSource(): and firends
- [ ] `T-56F554` (medium) the html in cardLink is proposterous
- [ ] `T-57EB02` (medium) the Create Next Event and Create Next Season buttons are repeated - refactor into a partial/fucntion
- [ ] `T-5CF784` (medium) the whole issue of how we fetch dates from db and pass them around needs thought - there are messy usages in ratings.php and Ratings Page
- [ ] `T-5EAEF1` (medium) We should outlaw the use of dot in mustache template variables - everything just one level deep nice and shallow - and then we don't pass objects in at all or use getObjectVarsCamelCase
- [ ] `T-607E4E` (medium) js should not just be lumped in places, components should say "i need this js"
- [ ] `T-60F2DC` (medium) submit is not a good name for a component that prints a table row
- [ ] `T-61C955` (medium) should templateresponse be abstract?
- [ ] `T-6986E3` (medium) the various textInput and other partials that combine a table row with a form element kinda suck
- [ ] `T-6C0F0E` (medium) tropy image with its inline max-wdith of 260px probably appears in more than one place and should be a component
- [ ] `T-72F4AA` (medium) the dropdowns are pushing down a line in deck search form
- [ ] `T-7821E6` (medium) When ErrorHandler is in JSON context like api.php it should not give the full html error but rather a JSON error. Similar for CLI. No need to spit out a full HTML page at the commandline if a db-upgrade call fails.
- [ ] `T-782E71` (medium) Don't connect to the db from SeasonDropDown or any Component
- [ ] `T-7FE77A` (medium) select.mustache and dropMenu.mustace are close to identical so combine them
- [ ] `T-82DF3D` (medium) ratingsTable doesn't need to be a separate component it's not reused
- [ ] `T-830024` (medium) the 25 records per page is a magic number
- [ ] `T-8838AF` (medium) you should be able to assign a bye on Match Listing when the event is inactive (instead it insists you give a result and an opponent) - would probably be a seaprate dropdown rather than an option in the existing cntrol
- [ ] `T-88DF2E` (medium) host slash maybe cohost is in three different templates
- [ ] `T-89F772` (medium) convert all partials from blahArgs to components, nest them, generally be cool
- [ ] `T-8BE434` (medium) DeckForm and DeckRegisterForm are very close copies of each other, let's extract one component
- [ ] `T-8FBFF6` (medium) Convert the rest of the DropMenus to be component that extend Dropmen
- [ ] `T-9514F3` (medium) it would be really nice to make actionResultComponent not an array
- [ ] `T-96382C` (medium) Some things like finalRoundsDropMenu should be of type DropMenu but are of type string
- [ ] `T-96CBE3` (medium) the AuthFailed page implies you are on the Event Host Control Panel which is a bit silly for such a generic name, also there is a Copmponent called AuthFailed whioch is confusing
- [ ] `T-9BA9E1` (medium) Recent decks is a component in one place and inline in deckForm.mustache
- [ ] `T-9F9305` (medium) some things are called selects and some things are called dropMenu - let's standardize on select
- [ ] `T-A0BB29` (medium) Push TemplateHelper::render into Component->render when we can
- [ ] `T-A36040` (medium) the way we spit out errors in deckSearch.mustache is unsafe probably use a component or (better) make DeckSearch return structured errors and turn that into html somehow or … something
- [ ] `T-A850EF` (medium) DeckSearch (page) and some of its compnents have too many args - make a search object that holds them all
- [ ] `T-AAB989` (medium) the html string in action.php is an abomination
- [ ] `T-ACDF58` (medium) **[start here]** It must be an errorr to provide or lookup a value ttha tisn't on the component/ in the context (in mustache rendering)
- [ ] `T-AEE3C8` (medium) the fact that you have to include the dropMenu template is still broken about partiaal rendering - the client programmer sholdn't have to care waht the thing is compsed of
- [ ] `T-B5B87F` (medium) partials/select is probably too general to be worth existing
- [ ] `T-B5C692` (medium) Should we store Event and friends on the Pages that use them as a getobjectvarscamelcase assoc array or as the object itself? we are inconsistent here. I think we've decided that objects are better than arrays, so maybe reverse the camel case stuff. But maybe Component objects are good (expose precisely what is necessary) but Model objects are bad (expose too much). Maybe all those Entry and Series things are about to become Components with partial templates to go with
- [ ] `T-BDE674` (medium) pages are components
- [ ] `T-BFDF90` (medium) it should be simpler to reg for an open/active event, no screen about the event should fail to offer you that option
- [ ] `T-C37E22` (medium) action.php doesn't go through mustache so it's an xss problem - maybe it should be a Page of some kind
- [ ] `T-C670E4` (medium) It would be nice to subsume the magic fonts icon stuff like iconClass in GameName into our Icon component so the caller doesn't have to care how that was achieved and we can avoid having both icon and iconClass properties in GameName
- [ ] `T-C6A34E` (medium) RecentDecksDropMenu is not a DropMenu because it's weird and javascripty - refactor?
- [ ] `T-CC6705` (medium) series-logo-img should be a component
- [ ] `T-D035FD` (medium) formatdropmenu and other components reach into the db but probably should not
- [ ] `T-D05CF7` (medium) there's some stuff that was shared in event.php lik ethe framing html that is no longer shared which is a shame
- [ ] `T-D274D3` (medium) pointsrule with its three way switch in the template is kind of horrible - refactor
- [ ] `T-D34AF6` (medium) displaydecks and displaymostplayeddecks should share code/template presumably
- [ ] `T-D58B1B` (medium) request and response dubously live in Views
- [ ] `T-D72026` (medium) selectInput is the last holdout on the select stuff move to dropmenu
- [ ] `T-DA3F25` (medium) series, home and profile (because of the search form) are the only pages that don't have the same grid div enclosure so probably make that part of header?
- [ ] `T-DBB020` (medium) some components like cardcounts are just too small and should be subsumed
- [ ] `T-E34295` (medium) it would be nice to call dropMenu from cardsetDropMenu.mustache but it isn't quite possible because of default disabled
- [ ] `T-E3829C` (medium) consider changing Views to Responses or similar
- [ ] `T-E4CAEE` (medium) figure out how to nest views within views?
- [ ] `T-E86DD9` (medium) BestEver and CurrentTrhough are pretty crappy components with only one use
- [ ] `T-EEE723` (medium) FormatsDropMenu has an inline style but could otherwise be converted to a dropMenu
- [ ] `T-F1985C` (medium) lint rule that models don't know about components then fix it all up
- [ ] `T-F4C004` (medium) it's wrong that you have to know which components are "really" dropMenus - just have them all have sutpid placehodllers
- [ ] `T-F708C6` (medium) the array types in BAndR component kinda suck because I generated them with array_map and did not call array_values on the result so i don't trust that they are truly lists like they should be but lets maybe check that out and change the type to list?
- [ ] `T-F92374` (medium) you inlined two fo the uses of print_submit in forgot.mustache rather than making a component but maybe it should be a component with a bunch of callers, similarly print_password_input's only use was inlined in that file - make components for form elements
- [ ] `T-FB17ED` (medium) Go back and make all trophies use the trophy component; I inlined the HTML of getTrophyImageLink in statsTable instead of making a component;
- [ ] `T-FC1075` (medium) the over 100 card count thing is not only repeated within component but also features again in tribalBandR

### ui (50) - design, layout, copy

- [ ] `T-041322` (medium) Event Settings is cramped at low width – probably the best solution is to have the labels stack on top of the form fields at narrower widths – that will require breaking it out of tables
- [ ] `T-094875` (medium) the empty medals view looks dumb now put it back how it was during the tourney or do somethign better
- [ ] `T-0E84B8` (medium) Contrast is insufficient in current events
- [ ] `T-150B12` (medium) format::getLegalCard is isnaely ineffciient
- [ ] `T-1554E7` (medium) display of is wonky was it always? make it better either way
- [ ] `T-1C4C00` (medium) Only show current standings if it would show something (r2+ in tourney, at least one match league)
- [ ] `T-1F6126` (medium) return to 16px font size and fix all squishedness OR fixed the bits of squishedness we still have becaus eof increased padding
- [ ] `T-2035B9` (medium) ratings.format should be a foreign but (1) we have fake format "Composite" and (2) SilverBlack is missing (got split into three?)
- [ ] `T-2C95B8` (medium) writing to the superglobals should be verboten, report.php is particuolarly ugly
- [ ] `T-333654` (medium) Magic Oonline Society Monthly Series says Most Recent Event 1v1 cEDH which is either wrong or we maybe souldn't show it on series.php?
- [ ] `T-35E887` (medium) more than one thing has the id=ds_select in decksearch html, and we use it in the css, and it's maybe the only reason we have id as a param, so just move it all to the class and remove the ids? there's no js right?
- [ ] `T-3654B6` (medium) the whole of formatcp is confused baout how things really work - private format my butt
- [ ] `T-394E98` (medium) Not even the controllers should access the superglobals - hide it all in a request object of some kind; the superglobals in getRequestContext suck - can we use post()->all() or post()->toString() or something?
- [ ] `T-3BB923` (medium) remove all center elements in favor of div class=c
- [ ] `T-4C4D09` (medium) We need to go further with date formatting - 1 hour ago and just past midnight in a few hours and midnight itself all kind of suck right now
- [ ] `T-55AA93` (medium) em using the color of a link is a mistake so remove emphasis-color and do somethign different tehre
- [ ] `T-625A0D` (medium) When you hit the upload trophy button with no file selected you go to default event view with no message – this is not new behavior but it is confusing/bad
- [ ] `T-657CB8` (medium) Decide what is going on about centering titles, use of center tag in general etc.
- [ ] `T-6AC794` (medium) display of cardscp is wonky - right align the numbres and date-ify the last updated at least
- [ ] `T-6B7AFA` (medium) Active Events on Player CP is squished even ta the new font size
- [ ] `T-73CE0C` (medium) typography on windows sucks
- [ ] `T-794052` (medium) the registration form uses bold tags instead of th; ratingsTable uses b in td instead of th
- [ ] `T-875FE2` (medium) the spacing in /admincp.php?view=calc_ratings is nonsense
- [ ] `T-87DE96` (medium) The commander legality checking and commander card finding in Format are completely bananas
- [ ] `T-8A80C8` (medium) So now just Home and Profile don't use the standard layout. Could we unify?
- [ ] `T-8D7F1A` (medium) Remove all nonstandard spacing from css, use only vars
- [ ] `T-8D8F5C` (medium) Report League Game Results has bad copy and bad design
- [ ] `T-8FFA73` (medium) the use of a box within a box in decksearch most played decks is a one-off and contrary to our design and should be redesgined
- [ ] `T-A2497C` (medium) The max-widths for the grid_x classes are fine but make no logical sense so let's break out of that old system entirely
- [ ] `T-ABD90F` (medium) Unreported matches button for event card
- [ ] `T-ACC831` (medium) the conflict between box (grants padding automatically) and card (does its own padding) could maybe be solved more elegantly
- [ ] `T-B32D68` (medium) changing your passowrd screen says "Passwords are required to be at least 8 characters long." even if you're flipping around between a reasonable password and another reasonable passowrd
- [ ] `T-B4A03B` (medium) comments on php.net PDOException docs (linked) show some improvements we could make
- [ ] `T-B5594B` (medium) the colors are not in wubrg order on decksearch form
- [ ] `T-C423A5` (medium) the logic we are using to show/hide "Players added after the event has started:" is not quite the same as the logic that determines whether to show a box or that or not
- [ ] `T-C5487E` (medium) Stop using float throughout and then get rid of the remaining .clear's and the css for same
- [ ] `T-C6765A` (medium) the layout on match listing of the text around stuff like "denotes a playoff/finals match" is not great
- [ ] `T-D2A9ED` (medium) th center alisn sucks on registration and presumably everywhere
- [ ] `T-D51CBA` (medium) the display of the form elements on in incorrect
- [ ] `T-D7F160` (medium) maybe hide the bye message in action.php if you have dropped the event even if it is still active?
- [ ] `T-D8040C` (medium) the event cards need a max width too - there's no point in letting them stretch a billion pixels - uless the design as a whole has a max width?
- [ ] `T-D92508` (medium) Put design system doc into the repo
- [ ] `T-D92787` (medium) Upcoming events on the homepage looks wonky since we went repsonsive (doesn't fill the box)
- [ ] `T-E42DC3` (medium) I don't think we're 100% consistent on showing a game name - when yo'uve dropped in the matchlisting you don't have your icon - maybe intentional? does any of it matter?
- [ ] `T-E7D7DC` (medium) there are a lot of tds with b tags that should be ths
- [ ] `T-EDC9B9` (medium) hastrophy has a count of trophies and that can't be right
- [ ] `T-F2BEDA` (medium) It would be nice to enable the three rules disabled in stylelint config (kebab case names and specificity order in the file)
- [ ] `T-6A4D09` (high) [redesign] times in series.php still suck (both regular time and next event) but cannot both be simplery reapclce by time element. But is this even what we want to be displaying? Overhaul everything!!! if keeping series.php or somethign like it then: next event on series cp can be the time stamp but should linke to the event. probably name + timestamp under?
- [ ] `T-D67081` (high) Responsive design
- [ ] `T-EF6098` (high) The event name is kinda unemphasized on eventreport now beause i removed an empty link around it (if it doesn't have an extrenal link, which it shouldn't even be linking to). Eventreport in general needs a massive hierarchy-aware redesign. A few simple improvements would make it massively better I suspect.

### infra (38) - CI, tooling, logging, config, deploys

- [ ] `T-F9036D` (low) setup phpcs to enforce singles if not reason for double quotes
- [ ] `T-FD18D1` (low) the config keys should be centralized as constants
- [ ] `T-0F758D` (medium) the two mostly empty arrays in every log message are kind of annoying
- [ ] `T-13C86D` (medium) On prod when you add a tribe to banlist you get redirected to cards restreict/ban/legal
- [ ] `T-19C39C` (medium) errors must log something at the top level - register_shutdown_function
- [ ] `T-205C6D` (medium) git pull (that is, deploy to prod) automatically on push
- [ ] `T-21E4E5` (medium) github.com/filp/whoops
- [ ] `T-25C8DF` (medium) override log level and other config settings with env variables
- [ ] `T-27D953` (medium) some kind of lint rule that == is bad
- [ ] `T-40DAF2` (medium) fix all the versions in composer to be greater than equal not star
- [ ] `T-40E262` (medium) **[start here]** Check for reoccurrence – github.com/PennyDreadfulMTG/gatherling/issues/908; Cannot get event for deck without event ID all over the logs, for lots of decks maybe, or more than one at least; this might be one or two bugs but – Trigger now in place to prevent this check logs for prevent_null_deck/"Attempt to set entries.deck to NULL". Figure out how decks get orphaned and stop it happening. This phenomenon has happened nine times since the backup was restored, so it's not just historical cruft. Maybe we should make it impossible – SELECT * FROM decks WHERE id NOT IN (SELECT deck FROM entries WHERE deck IS NOT NULL) – I no longer remember why I was checking for this unfortunately. Try and repro on local. Try and create a test that repros. Why didn't the trigger OR the canDelete stuff help? … Same issue is last time. Made League 16.05 inactive to figure out the playoffs. when I made it active, it dropped 3 players: SN4KE, Molorri, and W1LL. I've re-registered them and will add place holder decklists for now
- [ ] `T-4261CE` (medium) does updateLegalFormats think anything in wahatsinstadnard api is in standard even if actually rotated out long ago?
- [ ] `T-444C6A` (medium) We are using Log::info in Formats.php in places but that info should really be going to the screen so the admin can see it is working etc.
- [ ] `T-4B71DF` (medium) You need to be able to quickly and easily flip on debug logging to error_log (or the screen but that seems harder) during php unit run
- [ ] `T-5BEF5F` (medium) Get discord login working from local and from dev.gatherling.com
- [ ] `T-5DE217` (medium) make dev.gatherling.com autodeploy? Maybe gatherling.com too like pd
- [ ] `T-65D442` (medium) get rid of individually addressable files so tht we can have the url structure we want (lowercase) and still uppercase dirs for composer's case sensitive autoloading and prs-12's desire for PascalCase names
- [ ] `T-6D2AA7` (medium) configure styleci to do psr-21 and turn it back on
- [ ] `T-739066` (medium) we removed insertcardset and updatedefaultformats from readme and register too but you kinda have to do that for the site to be useful?
- [ ] `T-7A978B` (medium) There's some cool stuff in sqlstate.txt that would be good to log when we get a pdoexception in some way or contain in our dbexception or something
- [ ] `T-82978D` (medium) handle exceptiosn, group at top leve, log where you cactch them
- [ ] `T-865BB6` (medium) maybe the config helper signals to Marshaller or REquest or whatever it is that exceptiosn should be wrapped in an XException? rather than having to catch MarshalExeptions everywhere or let them fly
- [ ] `T-873F64` (medium) the timestamp of the logging is stupidly overprecise
- [ ] `T-876796` (medium) this is not useful behavior on dev – Fatal error: Maximum execution time of 30 seconds exceeded in /Users/bakert/gatherling/vendor/sentry/sentry/src/HttpClient/HttpClient.php on line 60
- [ ] `T-8E8B41` (medium) put all the docker init somewhere that docker runs automatically upon build, you shouldn't have to call insertcardset and such if using docker?
- [ ] `T-97EE0C` (medium) gatherling should have its own log
- [ ] `T-A049E7` (medium) there's a double timestamp n the prod logging because apache adds a timest mp too
- [ ] `T-A3E93E` (medium) it would be cool to change log level without a restart somehow or by manipulating querystring or something !
- [ ] `T-A77DF5` (medium) the way things show up in nginx error log is not right
- [ ] `T-BD5538` (medium) The way the log is just absolutely massive single file that never rotates is bad, it was better on the other machine
- [ ] `T-C29457` (medium) think about ConfigurationException now that MarshalException is a thing
- [ ] `T-C3C698` (medium) Maybe if you're an admin we console.log even on prod? that woudl be pretty cool esp for improting carsets? or maybe we have to print to screen for those
- [ ] `T-C479C3` (medium) ConfigurationException should accept the whole config and don't json_encode in that hacky way also it has passwords in so not suitable for logging
- [ ] `T-CDDBE5` (medium) not being able to dump autoload the stuff in docker mode sucks and interacts weirdly with local - maybe install composer in second stage too?
- [ ] `T-D89A99` (medium) formalize the logging DB prefix rather than just putting it in the string - extend to other areas
- [ ] `T-DFB1C6` (medium) we should probably log at info on prod but to somewhere else - like the "normal things happening log"?
- [ ] `T-F5E681` (medium) set up a scanner/linter that tells me if i do something like concanate an array onto a string
- [ ] `T-FA5FA5` (medium) We've lost a lot of logging now that begin and rollback don't call executeInternal - can we refactor a bit there or at least make sure we are solidly logging what we are doing

### arch (174) - structural refactors, naming, organisation

- [ ] `T-05A965` (low) we have ChangePassForm (user) and ChangePasswordForm (admin) and that's too close - rename the admin one to make it clear
- [ ] `T-4BA8E4` (low) maybe rename Db to Database now that it's not wildely used, or maybe after we finally kidll off models/Database
- [ ] `T-694EE1` (low) enforce single quotes
- [ ] `T-ED1EBA` (low) medalCount is hardcoded in assignMedalsByStandings in a way that's surprising to me but mayeb it's ok?
- [ ] `T-F1470F` (low) hardcoding how to make the manaSrc in FullMetagame sucks as do many things
- [ ] `T-FBE076` (low) hardcoding timezones is dumb - do we even use their timezone?
- [ ] `T-0004E1` (medium) some of the code in marshaller like return an empty list in the case of null for the list funcs is repeated - could we refactor nicely?
- [ ] `T-007DB6` (medium) Should Models and Data and such live under teh web root? not really
- [ ] `T-044010` (medium) a lot of the params of createEvent are strings but should not be strings and don't need to be strings
- [ ] `T-0640E2` (medium) action, report and CurrentMatchTable all try to find out who is opponent in simoilar unshared ways
- [ ] `T-06B7A7` (medium) EventDto, HostedEventDto, RatingsEventDto, UpcomingEventDto - rationalize this kind of stuff; the Deck variants too
- [ ] `T-071DBB` (medium) the use of new Rating in admincp to access what is essentially two static methods is dumb
- [ ] `T-0768D5` (medium) updateDeffaultFormats should not be a WireResponse
- [ ] `T-07CDB8` (medium) if a deck has a long name with no spaces it causes weird scrolling in deck.php when viewing and probably everywhere
- [ ] `T-0AB349` (medium) We could still make Current Events better by either splitting up started/not started into two sections or making it more clear "this is coming up" versus "this is overdue but has not been started' versus "this is in progress/has been started/here's where we are at"
- [ ] `T-0CF783` (medium) the loops for maindeck and sideboard in Deck->save are near-identical but got out of sync so that only sb had a bug. unify them
- [ ] `T-0E2F15` (medium) we go through some logic to add a cutoff class to season leaderboard but the visual effect is nothing
- [ ] `T-0E77BA` (medium) we could maybe have "last match" there when you're in an active tournament rather than nothing so it'd say something like "you had the bye r1" or something
- [ ] `T-10D2F6` (medium) Gatherling should "remember" you have a bye (for something like the 500) even if you unreg – don't tie byes to entries but rather to usernames
- [ ] `T-148F58` (medium) MatchTable and RecentMatchTable share a lot and can be combined I think
- [ ] `T-15728D` (medium) register shold return a result like login and then we can set session in the caller(s)
- [ ] `T-1828E7` (medium) we should just exit in authdebug, probably, but have some standardized way to say "get outta here" … we mostly use recirect but idk if that's approparite in that spot
- [ ] `T-1829FC` (medium) Linking to gatherer from card names kinda sucks in more than one way
- [ ] `T-186437` (medium) Further reafctor adding to legal list to also handle remove, and other lists, etc.
- [ ] `T-18962D` (medium) the case sensitivity issue with testicon/testIcon was crazy - need to defend against that
- [ ] `T-1BAAEA` (medium) maybe logo img should be a ocmponent
- [ ] `T-2129A4` (medium) Current standings on eventreport should be called swiss standings or something
- [ ] `T-243357` (medium) Do all the responses belong in a namespace? Probably make Gatherling\Responses and move most of what in Views out to that?
- [ ] `T-245C58` (medium) it's pretty ridic that you can reg with an email privacy of 2 which is not a meamingful value, but just remove the whole concept from the system instead of fixing
- [ ] `T-2783CC` (medium) **[start here]** we need to make the invalid palyer has a match thing impossible at the db level even though we cleared up the cause. Player A cannot play Player B in Event C if there isnt' an entry for A-C and A-B.
- [ ] `T-295D71` (medium) 127 as default seed is a magic number in a couple places
- [ ] `T-2B296B` (medium) the way we do trophySrc in TrophyTable is bats
- [ ] `T-2B5C85` (medium) We don't require a sensible password nor an email to reg or even to pass the captcha the whoel thing is a pile of lies
- [ ] `T-2B8B12` (medium) standardize on curlies or no curlies in string interpolation
- [ ] `T-2BA371` (medium) 'start' => $this->start->format('Y-m-d H:i:s'), needs to be shared code of some kind
- [ ] `T-2CED85` (medium) There should be a canoncial order to banned, legal, rstricted, restrictedtotribe and everything should follow it
- [ ] `T-31BEAF` (medium) we should not covnert argoth's name to the full Argoth, Sanctum of Nature/Titania, Gaea Incarnate under any circs
- [ ] `T-34E113` (medium) We don't have the inverse of dynamicCallOnStaticMethod where you call self:: instead of this, which seems way worse
- [ ] `T-38E7D9` (medium) notAllowed is a bad name if it's just something that isn't possible rn rather than a perms issue
- [ ] `T-395A43` (medium) restricted, banned and legal are very similar in BAndR so make them generic with componetns or somethign
- [ ] `T-3A113C` (medium) the Edit Player Information (profileEditForm) of profile.php is a kind of copy of the settings stuff on Player CP but different so unify
- [ ] `T-3C524E` (medium) You should be able to change a gathelring username without it breaking the world iron_lungs requested fanaticbass => toshiba1986 but I had to say no
- [ ] `T-3D5E2C` (medium) manualVerifyMtgoForm repeats some copy
- [ ] `T-3D85B2` (medium) eventreport is pretty stingy about letting you sign up and letting you know your status in that event – ideally we centralize the logic used in Player CP to show you a registration link if you can prereg, if late entry is still open, if it's a league; and that you ARE registered for that event if you are (maybe even with a report lihnk) or that you're reg'ed but your deck isn't valid and needs attention, etc. Instead it rather naïvely just checks if the event has started or not and if it allows prereg or not and only shows the link (even if you are already reg'ed) if both are true. It's a bit of a thorny refactor but worht doing. then we could tell that person on discord that we did it, too
- [ ] `T-449BB2` (medium) replace all redirects and header calls with Redirect and setHeader
- [ ] `T-45D53C` (medium) Is CardSet truly a model? Or just a helper? In Data not Models?
- [ ] `T-487461` (medium) should switch to mbstring ops
- [ ] `T-499CD6` (medium) We have a set_exception_handler in bootstrap now, but we want a set_error_handler too to give a nice 500 on PHP Fatal and similar
- [ ] `T-4AA153` (medium) is the definition of matchResult in UnverifiedPlayerCell slightly buggy in that it says "L" for a draw? Does that ever come up?
- [ ] `T-4C2F49` (medium) it should be simpler to reg for an open/active event
- [ ] `T-4CA3A3` (medium) think again about what is a trait and what is inheritance in REnderable, Repsonse, etc.
- [ ] `T-4FF125` (medium) you reverted your changes to Standings because it broke Player CP
- [ ] `T-5325B7` (medium) EventReportLink is a pretty feeble thing AND it has a propertycalled itself eventReportLink which sucks
- [ ] `T-546795` (medium) Share some code somewhere to give you a sane English description of round (Quarter-Finals not "Round 7" or "Finals Round 1")
- [ ] `T-56AD6F` (medium) tribalbandr has a bunch of rpetititon
- [ ] `T-570DEF` (medium) Someone signed up for gatherling with no username
- [ ] `T-573C98` (medium) **[start here]** shows everyone as seed 127 which is presumably not intentional. this seems to have been the case for years though. it's finalized events where the main rounds are single elim
- [ ] `T-589208` (medium) dev gatherling should be vidusally distinct in some way
- [ ] `T-59B90F` (medium) It needs to be easier to see player deck (with a mouseover?) for pd 500 commentary purposes
- [ ] `T-612106` (medium) timezone as a float make no sense yikes
- [ ] `T-61BCB8` (medium) Rationalize the case in Cardset v CardSet
- [ ] `T-664EC0` (medium) **[start here]** We allow trailing spaces in player username and it fucks things up see Discord and also trailing-spaces.txt
- [ ] `T-69C1AA` (medium) In Player we set some defaults when we define the properites and some things we set when name == '' in the constructor but surely we should just do one or the other?
- [ ] `T-6A0A35` (medium) It would be nice to eliminate getObjectVarsCamelCase we only ended up using it in 8 places and it's kind of obsfucating and sucky
- [ ] `T-6A129C` (medium) some of InfoTable's properties have bad names (line1 and the cryptic short ones)
- [ ] `T-6A28EF` (medium) updateDefaultFormats shouldn't really be a WireResponse I just did that to get rid of the echoes - need something that can be cli or cgi?
- [ ] `T-6BA6D5` (medium) Why do we have jquery? CAn we remove it in favor of vanilla js?
- [ ] `T-6DE757` (medium) the tribesTied stuff in Format is copy and pasted in two places
- [ ] `T-706FB0` (medium) Get rid of the concept of public email
- [ ] `T-716D5F` (medium) There should probably eventually be a way to add a player/report a result from the active event widget but I'm leaving that out of v1 because it's complicated
- [ ] `T-719D60` (medium) if you look at eventform there's a massive list of props and then a massive list of assignment to props in the constrcturo, can we do it with less duplciation? or just make there be less vars???
- [ ] `T-733B73` (medium) Maybe there should never be a "two-step" like there is in Create New Event in event.php - always push all hadling except request globals into helper funcs?
- [ ] `T-73E310` (medium) getCurrentLegalityOfCards needs updating to use array param not implode
- [ ] `T-74ED68` (medium) manualverifymtgoform has some silly copy
- [ ] `T-755A32` (medium) maybe the decklink in fullMetagame should be a deckLink, but it wasn't before
- [ ] `T-7603B3` (medium) We should try and get rid of colorStr or do it in a more sane way
- [ ] `T-776438` (medium) ConfiguraitonException should be centrlaized on startup not as we access the vars - stop dotting it all around the place and just fire it on startup if not configured
- [ ] `T-784626` (medium) the id in prereg.php is actually an int? always? we treat it as a string i think
- [ ] `T-79EC2D` (medium) setInitailSeed and setInitialByes could be one operation if that makes sense?
- [ ] `T-7ABB0B` (medium) There's a printf in masterplayerfile which is the same thing as an echo
- [ ] `T-803E91` (medium) standings->player should not be nullabel which would make getActiveOpponents simpler
- [ ] `T-8332E6` (medium) "paste stuff" and various other things are kinda silly for leagues with hundreds of entries (or even big tourneys) !
- [ ] `T-837962` (medium) It should be possible to answer "what decks are in the top 8" or more genearlly "what decks have not been eliminated" as an admin
- [ ] `T-83F885` (medium) Move the helper funcs in forgot.php to Login or similar
- [ ] `T-859AFF` (medium) it is very curious that we COMMIT in the middle of Deck->save and not at the end. I wonder if it serves any purpose?
- [ ] `T-8786BD` (medium) Gatherling lists adventure creatures under spells
- [ ] `T-8B5C80` (medium) Maybe we can simplify formatcp.main and even integrate handleActions?
- [ ] `T-8D0F63` (medium) The only thing in util is updateDefaultFormats - can we kill util now?
- [ ] `T-8D8FDC` (medium) you preferred mysql commandline restore because it was faster but then chickend out - measure?
- [ ] `T-8E1145` (medium) controlPanel and the menu in admincp should be shared code
- [ ] `T-8EB67D` (medium) handling of initial_byes and initial_seed is identical?
- [ ] `T-8FA47E` (medium) timezones list is repeated in player.php insanely as if it's a float
- [ ] `T-92F877` (medium) We should possibly defend against db params keys that contain commas and or apostrophes?
- [ ] `T-93F260` (medium) initArchetypeCount seems weird and bad maybe get rid of it
- [ ] `T-95F089` (medium) decksearch.main is kinda scuffed and could presumably be made nice failry easily
- [ ] `T-9730EB` (medium) Formats probably belongs in Data not Models
- [ ] `T-973402` (medium) Track what features are being used (or just use access_log) and retire stuff like decksearch or ratings history if it isn't being used
- [ ] `T-98AC41` (medium) formatcp->updateFormat takes _POST as an arg rather than passing 21 params in - find a better way
- [ ] `T-9A636D` (medium) forgot.php:main is so convoluted and Forgot has so many arguments that I feel we can do better
- [ ] `T-9ACE55` (medium) Maybe combine EventDto and HostedEventDto?
- [ ] `T-9CE930` (medium) action.php could be a lot better in many ways
- [ ] `T-9D5F25` (medium) I think the retry inside executeInternal is too naïve – it does an exceute rather than repeating the whole operation. Am I right?
- [ ] `T-9F074C` (medium) table layouts suck
- [ ] `T-9F2DD5` (medium) Could we remove jquery? Used in deck.js and probably other places? sorttable?
- [ ] `T-A0D32F` (medium) Maybe document stuff like Link means href and Src means src and other conventions
- [ ] `T-A64FB2` (medium) it's weird that the return value of ratingsData uses snake case keys
- [ ] `T-AD7B91` (medium) what FullMetagame does is entirely insane
- [ ] `T-ADC3F0` (medium) authFailed and insufficientPermissions are kinda similar?
- [ ] `T-AFD5BE` (medium) private_decks and others should be bool not int
- [ ] `T-AFE9D6` (medium) it's awkward that on allmatches while you are looking at your matches you cant' tell what you were playing
- [ ] `T-AFFE0F` (medium) testAssignMdealsByStanding passes pure gibberish into createEvent for struct
- [ ] `T-B07520` (medium) running updateDefaultFormats.php will mark as illegal some dfcs that should not be like Gumdrop Poisoner and Gutter Skulker
- [ ] `T-B3EC01` (medium) cardset => cardSet?
- [ ] `T-B50C52` (medium) The ratings.php should probably have Penny Dreadfful as first class (and maybe not some of the others)
- [ ] `T-B69772` (medium) allowsPlayerReportedDraws returns 1 or 2 which are both boolean true - this is completely insane
- [ ] `T-BA8B06` (medium) gathelring should accept both omenpath and spider-man names
- [ ] `T-BB61E8` (medium) when a deck was created is not that interesting (Deck search) maybe tie to tournament start time instead?
- [ ] `T-BE3207` (medium) eventList and playerEventList are the same thing with a couple diffeernt columns and buttons - combine
- [ ] `T-C1F756` (medium) we need to remove Pagination not accommodate it in that hacky fashion
- [ ] `T-C37C83` (medium) core block and extra cardsets are repetitious
- [ ] `T-C5CC56` (medium) insertCardIntoRestrictedToTribeList does not normaliseCardName but basically everything else does
- [ ] `T-C69663` (medium) FormatSuccess and FormatError are brothers and neither should probably exist
- [ ] `T-C89D55` (medium) make an edit event form object of some kind to group the giant number of fields/vars?
- [ ] `T-CB0B6E` (medium) maybe the various links should come from the objects not hardocindg how to do it all over the place? or from a link global?
- [ ] `T-CD2E9D` (medium) strcasecmp on season cast to string in getFilteredMatches is nutty. strcasecmp is in general very overused
- [ ] `T-CF5D04` (medium) showCreateNextEvent and showCreateNextSeason are a little package of repeated stuff
- [ ] `T-D1905F` (medium) activePlayersInfo and activePlayers is a bit messy in swissPariringBlossom
- [ ] `T-D1C66B` (medium) The way displayNameText is used in unverifiedPlayerCell is needlessly confusing - this applies to all uses of game name but where it is text it is most offensive
- [ ] `T-D1CA66` (medium) Login should return somethng and the caller should decide what to do, probably?
- [ ] `T-D478CD` (medium) how to form a deckLink is enocdded in a few places and should be centralized
- [ ] `T-D5673F` (medium) arch and pcg are bad names
- [ ] `T-D58FCF` (medium) The way we use POST/js to navigte player cp is insane
- [ ] `T-D5EFC6` (medium) Should active and finalized really be spearate? aren't there just N states and event's status can be in don't need 2+ vars?
- [ ] `T-D6BD58` (medium) loginName potentially returining false causes a lot of handling code - could we consider returning empty string for not logged in?
- [ ] `T-D6CFF4` (medium) the little dance around converting event->start to a timestamp before passing into Time is repeated code in several places
- [ ] `T-D77B74` (medium) Ratings is actually kind of unused, we bypass it for the most part, can we scrap it? or start using it?
- [ ] `T-D87169` (medium) the open and close empty para pattern in eventstandings (and elsewhere ?) sucks
- [ ] `T-DB4CDF` (medium) bestEver in ratings.php just arbitrarily returns one of thebest ever if there is a tie - instead return the oldest, or all fo them?
- [ ] `T-DC8D70` (medium) the construction for exists in Series::exists is a bit unwieldy - do something nicer in DB?
- [ ] `T-DDA414` (medium) 'New' and various other actions on formatcp should be GET requests not POST
- [ ] `T-DE0899` (medium) there's a harcoded 1000 in MissingTrophies that doesn't really make sense
- [ ] `T-DF669F` (medium) submitresultform and submitleagueresultform should be shared code not two seaprate things
- [ ] `T-E06A5B` (medium) the list of deck modifiers in formatSettings is crying out to be a loop
- [ ] `T-E16C7A` (medium) centralize all date formatting like that found in BestEver for conisstency – assignment is to highestRatingDate
- [ ] `T-E5A35A` (medium) the param lists in Standings->save are identical but slightly tricy to refactr into one
- [ ] `T-E73CB3` (medium) Ah, fun. Can I ask that you slip in the ability to assign 0-0 double losses
- [ ] `T-E86C25` (medium) unparsed_main and unparsed_side are accumulated in Deck->save but never used for anything?
- [ ] `T-E8F14A` (medium) you cna't unreg from an event that's "starting soon"?
- [ ] `T-E8FA57` (medium) all the unhandled exceptiosn
- [ ] `T-E90481` (medium) active tournaments should appear above active leagues in active events like we do with matches
- [ ] `T-EB226A` (medium) the wacky array or int behavior of the resturn value of seasonPointsTable is silly/bad/confusing
- [ ] `T-EBB181` (medium) getDeckColors is really insane
- [ ] `T-EBE3E9` (medium) things like getCastingCosts could be positive-int and all that stuff
- [ ] `T-EC1CE6` (medium) move js to bottom, defer
- [ ] `T-EC473F` (medium) the main of report should be broken up into helpers
- [ ] `T-ED81DF` (medium) some places we say Win Loss Bye others we say W L can we regularize?
- [ ] `T-EEC01B` (medium) 8am is a bad string for 3 hours ago even though it is strictly true what should we say there? This morning 8am? 3 hours ago? Simialr for "Today 4pm" at night time for an event that ended hours ago.
- [ ] `T-EF55F9` (medium) the whole "Deckmaster74 is tenderloin and sometimes we show one and sometimes the other" thing needs making consistent. Probably always show modo name but some visual indicator their gatherling name is different and a tooltip?
- [ ] `T-F0E970` (medium) ErrorMessage is not a good name for the thing in formatcp if it contains success messages too (which should be green?)
- [ ] `T-F18546` (medium) player settings editing sucks, as does switching to settings tab
- [ ] `T-F19408` (medium) it's possible that mostRecentHostedEvent should be nested in playerInfo in Home
- [ ] `T-F1B814` (medium) public (1) and private (0) for email status must be an enm or a bool
- [ ] `T-F360D2` (medium) Should all our private vars actually be protected? Probably
- [ ] `T-F75E0D` (medium) Should stuff that we directly and simply derive from event (such as in PlayerList's constrcutr) could live on Event instead. Decide how much we want to make a dict of flat values versus passing around objects
- [ ] `T-FAC806` (medium) There are some things (frame, controlPanel) duplicated in the templtaes for all the EventFrame descendants - should we be more DRY?
- [ ] `T-FBC1BE` (medium) I'll take a look later and see if we can just remove that requirement – the need to be verified before being a series organizer – if there's no way to get verified we should remove it? Or is getting an admin to verify a good step?
- [ ] `T-FBDE0D` (medium) Replace all the old db calls with new db calls, or at least get rid of those exits
- [ ] `T-FC7FBC` (medium) the branches of the if in admincp.main should really be helper functions that return a string of the result would be way cleaner
- [ ] `T-FD6674` (medium) Next event on series cp should be name-as-link + date and time not just date and time (no link)
- [ ] `T-FEC280` (medium) If I make the series in the two testAssign... methods in EventTest use the same name I get an integrity constraint violation – why aren't they isolated from one another?
- [ ] `T-2828A7` (high) **[start here]** Can't rename admin to Admin because of web addresses so we need to move to an index.php that does the routing before we do that
- [ ] `T-48758A` (high) In 164d2c0 you removed some output from recalculating ratings without replacing it with anything, and that stuff won't run to completion in a timely fashion anyway
- [ ] `T-49444B` (high) deck.main is fairly hair-raising and could be refactored to be simpler
- [ ] `T-E90200` (high) better 500 (and other error) pages with centrailzed routing
