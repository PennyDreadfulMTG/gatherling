<?php
    include 'lib.php';
    include 'config.php';
    session_start();
    print_header('Archetype Descriptions');
    // TODO: This file is called from function deckForm and function deckRegisterForm of deck.php. Need to add $_POST
    // passing of to and from this file of (name, archetype, maindeck, sideboard, and comments fields) so that players
    // do not loose their progress during deck entry.
    //
    // Also need to populate this page with data from the archetypes table in the database. Name would be the heading
    // and description field would go between the <p>
    ?>

    <div class="grid_10 suffix_1 prefix_1">

        <div class ="gatherling_news box">
            <center><h3>Aggro</h3></center>
            <p>
                Aggro (short for "aggressive") decks attempt to reduce their opponents from 20 life to 0 life 
                as quickly as possible, rather than emphasize a long-term game plan. Aggro decks focus on 
                converting their cards into damage; they prefer to engage in a tempo-based race rather than a 
                card advantage-based attrition war. Aggro generally relies upon creatures as a cumulative source 
                of damage. While strategically simple, aggro decks can quickly overwhelm unprepared opponents 
                and proceed to eke out the last bit of damage they need to end the game. Aggro decks also generally 
                have access to disruptive elements, which can inhibit the opponent's attempts to respond.
            </p>            
        </div>        

        <div class ="gatherling_news box">
            <center><h3>Combo</h3></center>
            <p>
                Combo decks utilize the interaction of two or more cards (a "combination") to create a powerful effect 
                that either wins the game immediately or creates a situation that subsequently leads to a win. The term 
                "combo" can also describe a deck built around resolving a single powerful spell such as Tooth and Nail 
                to create the same kind of insurmountable advantage. Combo decks value power, consistency, and speed: 
                the combo should be strong enough to win, the deck should be reliable enough to produce the combo on a 
                regular basis, and the deck should be able to use the combo fast enough to win before the opponent.
            </p>
        </div>

        <div class="gatherling_news box">
            <center><h3>Control</h3></center>
            <p>
                Control decks avoid racing and attempt to slow the game down by executing an attrition plan. These decks 
                attempt to accumulate resource advantage, contain threats, and run opponents out of options. The primary 
                strength of control decks is their ability to devalue the opponent’s cards. They do this in four ways:
            </p>
            <ol>
                <li>
                    Erasing threats at a reduced cost. Given the opportunity, Control decks can gain card advantage by 
                    answering multiple threats with one spell, stopping expensive threats with cheaper spells, and 
                    drawing multiple cards or forcing the opponent to discard multiple cards with one spell.
                </li>
                <li>
                    Not playing threats to be answered. By playing few proactive spells of their own, control decks gain 
                    virtual card advantage by reducing the usefulness of opposing removal cards.
                </li>
                <li>
                    Disrupting synergies. Even if control decks do not deal with every threat directly, they can leave 
                    out whichever ones stand poorly on their own; e.g., a creature enchantment which will never need 
                    attention if all enemy creatures are quickly removed.
                </li>
                <li>
                    Dragging the game out past opposing preparations. An opponent's faster, efficient cards will become 
                    less effective over time.
                </li>
            </ol>
            <p>
                Often control decks end the game with the very same threats midrange or ramp decks use (see below). The 
                difference is that they're not focused on getting those threats out as soon as they possibly can. 
                Instead, they use them to mop up a game they've already secured and stabilized. Alternatively, the large 
                threat itself can be used as a tool to stabilize, either by virtue of its size or its ability to remove 
                threats.
            </p>
        </div>
        
        <div class ="gatherling_news box">
            <center><h3>Aggro-combo</h3></center>
            <p>
                Aggro-combo is a hybrid archetype that employs aggressive creature strategies along with some combination 
                of cards that can win in "combo" fashion with one big turn. For instance, Ravager Affinity decks that 
                include Disciple of the Vault can win by attacking with creatures and also with a combo finish of 
                sacrificing multiple artifacts to Arcbound Ravager and killing the opponent with Disciple triggers.
            </p>
        </div>        
        
        <div class ="gatherling_news box">
            <center><h3>Aggro-control</h3></center>
            <p>
                Aggro-control is a hybrid archetype that contains both aggressive creatures and control elements. 
                These decks attempt to deploy quick threats while protecting them with light permission and disruption 
                long enough to win. These are frequently referred to as "tempo" strategies, as their control elements 
                are often more temporary; for instance, they may return opposing creatures to their owners' hands 
                rather than remove them entirely.
            </p>
        </div>        
        
        <div class ="gatherling_news box">
            <center><h3>Combo-control</h3></center>
            <p>
                Control-Combo is a control deck with a combo finisher that it can spring quickly if need be. A notable 
                subtype of Control-Combo is "prison," which institutes control through resource denial (usually via a 
                combo).
            </p>
        </div>        
        
        <div class ="gatherling_news box">
            <center><h3>Midrange</h3></center>
            <p>
                Midrange tends to feature one-drops with abilities (e.g., Llanowar Elf) and early threats that are more 
                defined by their resilience than their raw size, speed, and power. These decks tend to be a turn slower 
                than the aggro decks, although still reasonably fast, and oftentimes use Planeswalkers to generate 
                advantage on the battlefield. They will sometimes use a few reactive cards to deal with key threats, but 
                tend to be at a disadvantage if they draw too many of this type of card and are unable to develop their 
                board. Some midrange decks trend toward the aggressive end of the spectrum, and others toward control. 
                What they hold in common is their focus on accumulating advantage on the battlefield itself, as opposed 
                to gaining an advantage in raw resources (having a 4/4 versus a 2/1, as opposed to having two cards in 
                hand versus a single card, for example).
            </p>
        </div>        
        
        <div class ="gatherling_news box">
            <center><h3>Ramp</h3></center>
            <p>
                Ramp decks tend to spend their early turns developing their mana advantage instead of deploying threats 
                to the board in an attempt to play larger more powerful mana-advantage spells (often spells that have an 
                X in the casting cost). In order to be successful the card that provides the win condition needs to have 
                a greater return than several smaller spells that can be played faster. Ramp decks rely upon one or two 
                threats to do a lot of work for them.
            </p>
        </div>        
        
    </div>

    <?php print_footer(); ?> 
