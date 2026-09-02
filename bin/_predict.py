import re, subprocess, functools

STOP = set('''the this that with from have has had will would should could been being does
what when where which while there here they them then than your you our are was were and but
for not all any one two out its it's don't can't doesn't isn't shouldn't kinda sucks stuff
thing things maybe probably really very just only also more most some much many way ways
need needs make made take takes give gives show shows use used using like about into over
under after before both each other same such own too very still even back down off own'''.split())

MODELS = ('deck','event','player','series','format','match','entry','standing',
          'rating','card','tribe','season','round','pairing','subevent')

@functools.lru_cache(maxsize=1)
def index():
    """Two indexes: strong (class + file basenames) and weak (function names)."""
    strong, weak = {}, {}
    files = subprocess.run(['git','ls-files','gatherling','tests'],
                           capture_output=True, text=True).stdout.split()
    for f in files:
        if not f.endswith(('.php','.mustache')):
            continue
        base = f.split('/')[-1].rsplit('.',1)[0].lower()
        strong.setdefault(base, set()).add(f)
        if not f.endswith('.php'):
            continue
        try:
            src = open(f, encoding='utf-8', errors='ignore').read()
        except OSError:
            continue
        for m in re.finditer(r'\b(?:class|trait|interface|enum)\s+(\w+)', src):
            strong.setdefault(m.group(1).lower(), set()).add(f)
        for m in re.finditer(r'\bfunction\s+(\w+)', src):
            weak.setdefault(m.group(1).lower(), set()).add(f)
    return strong, weak

def predict(text, limit=8, min_score=3):
    strong, weak = index()
    score = {}
    def add(key, s, w):
        for f in s.get(key, ()):
            score[f] = score.get(f, 0) + w

    for c in re.findall(r'\b([a-z][a-zA-Z0-9_]*\.php)\b', text):        # event.php
        add(c[:-4].lower(), strong, 6)
    for c in re.findall(r'\b([A-Z][a-zA-Z0-9]{2,})\b', text):           # Standings, DeckForm
        add(c.lower(), strong, 4); add(c.lower(), weak, 2)
    for c in re.findall(r'\b([a-z]+(?:[A-Z][a-zA-Z0-9]+)+)\b', text):   # getFinalResults
        add(c.lower(), weak, 4); add(c.lower(), strong, 1)
    for c in re.findall(r'\b([a-z]{4,})\b', text):                      # standings, ratings
        if c not in STOP:
            add(c, strong, 2)
    for a, b in re.findall(r'\b(\w+)\s*->\s*(\w+)', text):              # Deck->save
        add(a.lower(), strong, 4); add(b.lower(), weak, 4)

    # Domain nouns hide inside compounds: "maindeck" and "decklist" both mean Deck.
    words = set(re.findall(r'\b(\w{4,})\b', text.lower()))
    for noun in MODELS:
        if any(noun in w and noun != w for w in words):
            add(noun, strong, 2)

    ranked = sorted(score.items(), key=lambda kv: (-kv[1], kv[0]))
    top = [f for f, s in ranked if s >= min_score][:limit]
    return top or [f for f, _ in ranked[:2]]
