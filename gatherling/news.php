<ul>Deck Model Update!
    <li>Added deck validator that interfaces with format system to make report if decks are legal for a format.</li>
    <li>Created a ban list and a legal list to allow legality rules of individual cards.</li>
    <li>Can specify what rarity of cards are legal. So pauper event can have only common cards legal for example</li>
    <li>Added error tracking and reporting system for the deck database to identify legality issues</li>
    <li>Added additional properties and functionality inside the model to be able to trap additional deck legality issues</li>
    <li>Added a deck Errors section to the deck view with accurate descriptions of problems so players can easily fix the format issues</li>
    <li>Added card counts for all headings in deck view so players can more easily see how a deck is constructed</li>
    <li>Updated deck links so that illegal decks show up in red, missing decks show up in yellow, and good decks show up in green</li>
</ul>
<ul>Deck Validator reports following errors currently. These errors are reported for both maindeck cards and sideboard cards. More will be added.
    <li>Verifies that cards are in the allowed sets for the format</li>
    <li>Verifies that cards aren't on the banned list or that they ARE on the legal list</li>
    <li>Verifies that cards are legal not just by rarity, but that they are of the correct rarity in the allowed sets</li>
    <li>Verifies that decks are at least 60 cards. Later will add ability to specify deck size</li>
    <li>Verifies that sideboards are 0 cards, 15 cards, or 1 card (Commander) in size</li>
    <li>Many more verifications coming!</li>
</ul>
<ul> Database Model Optimization!
    <li>Added database optimization code that will be deployed to various othe models</li>
</ul>
<ul>Standings Model Update!
    <li>Cleaned source code and optimized the Standings model</li>
    <li>Fixed league standings scoring system</li>
</ul>
<ul>Entry Model Update!
    <li>Disabled ignore decks functionality for when players failed to enter a deck list. All deck lists are required at Gatherling.com. Still have some clean-up to do here.</li>
    <li>Cleaned up and optimized source code</li>
</ul>
<ul>Player Model Update!
    <li>Disbled ignore decks functionality for when players failed to enter a deck list. All deck lists are required at Gatherling.com. Still have some clean-up to do</li>
    <li>Fixed authorization access to Host CP for Series Organizers.</li>
    <li>Fixed deck editing authorizations for Series Organizers.</li>
    <li>Players can now enter their own deck lists after an event starts. This makes it easier for late players to join.</li>
    <li>Players can now fix errors with their own deck lists after an event starts.</li>
    <li>Changed every instance of Steward to organizer to reflect the actual name of Series Organizers</li>
    <li>Cleaned up and optimized source code</li>
</ul>
<ul>Series Model Update!
    <li>Changed every instance of Steward to Organizer to refelct the actual name of Series Oganizers</li>
</ul>
<ul>Event Model Update!
    <li>Cleaned up and optimized source code</li>
    <li>Changed all instances of steward to organizer to reflect the relationship with the Series Organizer</li>
</ul>
<ul>Match Model Update!
    <li>Cleaned up source code. Brutally murdered white space. Added optimization code.</li>
</ul>
<ul>Subevent Model Update!
    <li>Cleaned up source code. Brutally murdered white space. Added optimation code.</li>
</ul>
<ul>Admin CP Update!
    <li>Added form to create new series</li>
    <li>Added System Format Editor</li>
    <li>Fixed card set entry form bug that didn't properly assign set type to new sets being added</li>
</ul>
<ul>Series CP Update!
    <li>Added a Format Editor Form so that Series Organizers can create series filters to create rules for what is legal in deck lists for their events</li>
</ul>
<ul>Host CP Update!
    <li>Added functionality to drop players with illegal or missing deck lists from events once the event starts</li>
    <li>Fixed "link to nowhere" bug that would drop a player to an empty gatherling page after entering deck list</li>
</ul>
<ul>Player CP Update!
    <li>New Column that appears when ever a player has a missing or illegal deck list. That column lists all decks that are missing or have errors so players can more easily fix them. </li>
</ul>
<ul>Series Report
    <li>Cleaned up and optimized source code. Brutally murdered white space.</li>
</ul>
<ul>Gatherling Graphics update!
    <li>Updated Gatherling Header image!</li>
</ul>
<ul>New Archytpes Page!
    <li>Created an informational page about deck archetype that for now I am just linking to from the main page. But eventually will embed into Gatherling in the deck construction page so that players can be better prepared to list their deck archetype! Thus making Gatherling's Metagame reports more accurate.</li>
</ul>