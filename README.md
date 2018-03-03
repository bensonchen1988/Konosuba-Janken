# Konosuba-Janken
PHP practice!

This is a small personal project to practice PHP with.

A Konosuba themed Rock-Paper-Scissors game, with absolutely horrendous UI because I can't CSS (yet)!

At the moment this game uses cookies to store state, so it is veeeeeery easily hacked right now.   
Will move to database for that in the future, which will then allow validations with the server side previous state.  

Current game rules:  
Rock beats Scissors  
Paper beats Rock  
Scissors beats Paper  
EXPLOSION beats everything, but ties with another EXPLOSION  

EXPLOSIONs are earned everytime the player loses 3 times in a row, upon which the lose streak counter is resetted.  
Hover your mouse over it to hear a random Megumin explosion related sound clip!   
(sound clips stolen from the awesome Megumin soundboard site, check it out here!: https://megumin.love/soundboard )

Players have the following stats based on their level (currently no variations nor allocatable points etc):  
ATK - Damage you do to the enemy upon winning an RPS round.  
HP - How much damage you can take before you die.  
DEF - Directly mitigates damage in the form of enemyATK - yourDEF, to a minimum of 1 damage.   
CRIT - % chance of dealing DEF ignoring double damage.

Upon defeating enemies, players now recover a small amount of HP equal to a fraction of the defeated enemy's max HP.

Current max level - 99

Future features:  

-Randomize encounters to be within a range of player level  
-Skill points upon leveling up, and usage of skill points to unlock skills  
-Equipment drops from defeating monsters. Equipment may simply grant stats, or special effects such as "winning with Rocks lets you deal double damage"
