# Konosuba-Janken
PHP practice!

This is a small personal project to practice PHP with.

A Konosuba themed Rock-Paper-Scissors game, with absolutely horrendous UI because I can't CSS (yet)!

At the moment this game uses cookies to store state, so it is veeeeeery easily hacked right now. 
Will move to database for that in the future, which will then allow validations with the server side previous state.

Current game rules:
Rock beats Paper
Paper beats Scissors
Scissors beats Rock
EXPLOSION beats everything, but ties with another EXPLOSION

EXPLOSIONs are earned everytime the player loses 3 times in a row, upon which the lose streak counter is resetted.
Hover your mouse over it to hear a random Megumin explosion related sound clip! 
(sound clips stolen from the awesome Megumin soundboard site, check it out here!: https://megumin.love/soundboard )

Players have the following stats based on their level (currently no variations nor allocatable points etc):
ATK - Damage you do to the enemy upon winning an RPS round.
HP - How much damage you take before you die.
DEF - Directly mitigates damage in the form of enemyATK - yourDEF, to a minimum of 1 damage. 
CRIT - % chance of dealing DEF ignoring double damage.

Players currently do not recover HP outside of dying or leveling up.

Current max level - 6

Future features:

-Randomize encounters to be within a range of player level
-Means of regenerating HP, probably via defeating monsters
-Skill points upon leveling up, and usage of skill points to unlock skills
