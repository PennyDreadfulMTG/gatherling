<?php
$gatherlingoutofservice = 0;
if ($gatherlingoutofservice != 1) {
    include 'lib.php';
    include 'config.php';
    session_start();
    print_header('Home'); ?>

    <div id="maincolumn" class="grid_8">

    <div class="gatherling_news box">
        <center><h3>Gatherling Special Update</h3></center>
        <p>
            By <a href="http://www.gatherling.com/profile.php?player=silasary&mode=Lookup+Profile">silasary</a><br />
            December 21st, 2017
        </p>
        <p>
            After a year of running Penny Dreadful tournaments and dealing with Gatherling's quirks,
            I present you with Gatherling 4.8.0, an attempt to improve the Quality of Life for Hosts and Players alike.<br />
            I have upgraded the format editor to be a much nicer experience, cleaned up the codebase significantly,
            and made the deck editor a much nicer (and more forgiving) experience.<br />
            Full list of changes:
            <h4>Player Control Panel</h4>
            <ul>
              <li>Dynamically display Ratings based on formats you've actually played.</li>
              <li>Rearranged things so that the Active Matches/Submit Results prompt is at the top of the page.</li>
              <li>Display Magic Online chat room for active events</li>
              <li>Fixed account verification</li>
            </ul>
            <h4>Event Control Panel</h4>
            <ul>
              <li>Added option to allow player-submitted Late Entries (Up to a specified round)</li>
              <li>Default page is now context aware- If event is in progress, jump straight to Match Listing.</li>
              <li>Tooltips! There are a lot of weird checkboxes without much explanation, so I added tooltips to them.</li>
              <li>Fixed bug where a guest host cannot modify settings if they own a different series.</li>
            </ul>
            <h4>Deck Editor</h4>
            <ul>
              <li>Added Support for more liagatures, both forms of split card notation, and other common name variations.</li>
              <li>Added ability to load decklist from file.</li>
              <li>When submitting a late entry deck, players may continue to edit their decks until there are no errors</li>
            </ul>
            <h4>Format Control Panel</h4>
            <ul>
              <li>This is a separate control panel now!</li>
              <li>Split Format Editor into separate tabs, which was sorely needed for any format with
                  <a href="https://scryfall.com/search?q=f%3Apd">10,000+</a> explicitly defined legal cards.</li>
              <li>Better support for Eternal Formats - You can now tick a checkbox that means "All sets are legal".</li>
              <li>Added an "Add all cardsets" button, if Eternal mode doesn't quite work for you.</li>
              <li>Increased speed of inserting legal cards, so you no longer need to insert those ten-thousand cards 500 at a time.</li>
              <li>Added some sensible defaults when creating a new format.</li>
            </ul>
            <h4>Admin Control Panel</h4>
            <ul>
              <li>Further improved UX for adding new sets.</li>
              <li>Ability to manually verify players</li>
              <li>Ability to rebuild tribal database</li>
            </ul>
            More things to come soon!
        </p>

    </div>

    <div class="gatherling_news box">
        <center><h3>Gatherling Special Update</h3></center>
        <p>
            By <a href="http://www.gatherling.com/profile.php?player=Longtimegone&mode=Lookup+Profile">Longtimegone</a><br />
            April 19th, 2016
        </p>
        <p>
            I have just finished rewriting the set import tool to allow it to directly scrape the new set data. This means
            that all previously missing sets have been added, and new sets will be as simple as a couple of clicks to import
            in the future so they should always be up by the time cards are online.
        </p>

    </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                April 1st, 2015
            </p>
            <p>
                Dragons of Tarkir is now in Gatherling. This will probably be the last time I enter a set as
                <a href="http://www.gatherling.com/profile.php?player=longtimegone&mode=Lookup+Profile">longtimegone</a>
                is taking over the duty.
            </p>
            <p>
                I would also like to take a moment and thank everyone who took the time to express their
                appreciation for my contribution to the PRE community over the years.
                I was really overwhelmed with people just thanking me once I announced I was stepping down
                with Gatherling.com. My main mission when I made Gatherling.com was that
                I just wanted a cool place for cool people to hang out and play competitive fun magic. I think
                by and large we accomplished that together. So as much as I appreciate all the thanks I have
                received, I just wanted to thank everyone who uses Gatherling.com and remind you that it
                wouldn't be as fun or successful without all of you. And Gatherling.com wouldn't be nearly
                as functional without the team of programmers who helped me over the years, including longtimegone.
                So thank you PRE community! Running this site was the most fun I had playing on MTGO. And I am not
                going away completely. I will still be in the background helping out as it is needed. So rest assured
                that Gathering.com will be around for a while.
            </p>
            <p>
                I hope you enjoy the new set! And again here is the tutorial video for you Series Organizers who will
                need to add this set to your format, but who can't remember how.
            </p>
            <center><iframe width="560" height="315" class="inputbox" src="http://www.youtube.com/embed/r0oVzxH8Jpo" frameborder="0" allowfullscreen></iframe></center>
            <br />
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                February 17th, 2015
            </p>
            <p>
                All good things must come to an end. And likewise my time at Gatherling.com is coming to an end as well.
                With everything that is going on in my life, I no longer have the time to maintain Gatherling.com. So
                I am opening it up to any competent person who would like to take over Gatherling.com, please contact me.
                Make sure you are ready for the commitment, myself and many others have put a lot of work into Gatherling.com
                coding and marketing it to get it to what it is today. I do not want to see this site end as I am sure
                no one reading this does either. You may send me a request to take over the site
                <a href="http://www.gatherling.com/message.php?mode=Send&type=Update%20Request">here</a>.
            </p>
            <p>
                In your request please tell me about what you think qualifies you to take over the site as well as why you
                would like to do it. Thanks for all the memories here at Gatherling.com!
            </p>
            <br />
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                February 4th, 2015
            </p>
            <p>
                First set of 2015! Fate Reforged is now in Gatherling. I have a new source for creating the installation
                files that greatly increased the speed I was able to prepare the installation file this time around.
                Only a couple hours compared to days the other way. Still not as fast as our old automated import
                program was, but getting more efficient. Yay us!
            </p>
            <p>
                I hope you enjoy the new set! And again here is the tutorial video for you Series Organizers who will
                need to add this set to your format, but who can't remember how.
            </p>
            <center><iframe width="560" height="315" class="inputbox" src="http://www.youtube.com/embed/r0oVzxH8Jpo" frameborder="0" allowfullscreen></iframe></center>
            <br />
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                November 22nd, 2014
            </p>
            <p>
                Again thanks to the efforts of the tireless longtimegone, we now have the Commander 2014 set in Gatherling!
                (Where does he find the time for all this?) I wanted to give a special thanks to longtimegone for putting in the extra effort and getting
                this new set ready. As well a special thanks for _Kumagoro_, for continueing to keep us informed. Invaluable!
                Commander 2014 can now be used simply by adding it to the list of legal sets
                for each filter.
            </p>
              <p>
                I hope you enjoy the new set! And again here is the tutorial video for you Series Organizers who will
                need to add this set to your format, but who can't remember how.
            </p>
            <center><iframe width="560" height="315" class="inputbox" src="http://www.youtube.com/embed/r0oVzxH8Jpo" frameborder="0" allowfullscreen></iframe></center>
            <br />
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                October 2nd, 2014
            </p>
            <p>
                Thanks to the efforts of the tireless longtimegone, we now have the newest card set in Gatherling!
                I wanted to give a special thanks to longtimegone for putting in the extra effort and getting
                this new set ready.Khans of Tarkir can now be used simply by adding it to the list of legal sets
                for each filter.
            </p>
              <p>
                I hope you enjoy the new set! And again here is the tutorial video for you Series Organizers who will
                need to add this set to your format, but who can't remember how.
            </p>
            <center><iframe width="560" height="315" class="inputbox" src="http://www.youtube.com/embed/r0oVzxH8Jpo" frameborder="0" allowfullscreen></iframe></center>
            <br />
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                July 29th, 2014
            </p>
            <p>
                As long as I have been playing Magic Online you might think that I would remember that a new core set
                comes out every July. I didn't and so I was unprepared on the release date. However I would like to
                give special thanks to player longtimegone for assisting me with preparing the Magic 2015 update
                that is now installed here on Gatherling.
            </p>
            <p>
                Remember again that after Wizards of the Coast updated their Gatherer database, they omitted the
                Text Spoiler feature that Gatherling and many other sites use around the net to import the latest card
                set. So myself and longtimegone had to manually create the needed import file in order to get
                Magic 2015 into the Gatherling database.
            </p>
            <p>
                I hope you enjoy the new set! And again here is the tutorial video for you Series Organizers who will
                need to add this set to your format, but who can't remember how.
            </p>
            <center><iframe width="560" height="315" class="inputbox" src="http://www.youtube.com/embed/r0oVzxH8Jpo" frameborder="0" allowfullscreen></iframe></center>
            <br />
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                July 1st, 2014
            </p>
            <p>
                Vintage Masters is now in the Gatherling database, after much work. I had to manually type out the
                installation file this time around since Wizards changed the output method we needed from Gatherer
                and made our card set import automation program obsolete. Thanks for that! hehehe. Have fun!
            </p>
            <p>
                Again, sorry for the lateness of this update, and thank you for your understanding.
            </p>
        </div>

        <div class="gatherling_news box">
            <center><h3>Gatherling Special Update</h3></center>
            <p>
                By <a href="http://www.gatherling.com/profile.php?player=Dabil&mode=Lookup+Profile">Dabil</a><br />
                June 20th, 2014
            </p>
            <p>
                I just wanted to say I am sorry that I have not added Vintage Masters to the Gatherling database yet.
                First of all I was not aware of the set being released since I no longer play M:TG any longer. Second,
                because of changes that Wizards made to the Gatherer database, I cannot simply upload the set into
                Gatherling's database using automated functionality as usual. I will either have to manually enter
                the new set, or else create the import data manually. Either way, it will be much more time consuming
                going forward to enter new sets. I am hoping to have Vintage Masters added to the Gatherling database
                by next week.
            </p>
            <p>
                Sorry for the lateness of this update, and thank you for your understanding.
            </p>
        </div>

    </div>

    <div id="sidecolumn" class="grid_4">
        <div class="clear"></div>
        <?php include 'sidebar.php'; ?>
        <div class="clear"></div>
    </div> <!-- sidecolumn -->

    <?php print_footer(); ?>

<?php
} else {
        require 'outofservice.php';
    }
?>
