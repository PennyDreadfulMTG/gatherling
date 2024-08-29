<?php

namespace Gatherling;

$DEBUG = null;
$CHECK_DELTA = false;

$CHECK_OPTIMUM = true;

class MaxWeightMatching
{
    private $maxcardinality;
    private $edges;
    private $nedge;
    private $nvertex;
    private $endpoint;
    private $neighbend;
    private $mate;
    private $label;
    private $labelend;
    private $inblossom;
    private $blossomparent;
    private $blossomchilds;
    private $blossombase;
    private $blossomendps;
    private $bestedge;
    private $blossombestedges;
    private $unusedblossoms;
    private $dualvar;
    private $allowedge;
    private $queue;
    private $bestedgeto;

    public function __construct($edges, $maxcardinality = false)
    {
        $this->edges = $edges;
        $this->maxcardinality = $maxcardinality;

        if (!$this->edges) {
            return;
        }

        $this->nedge = count($this->edges);
        $this->nvertex = 0;
        foreach ($this->edges as $edge) {
            list($i, $j, $w) = $edge;
            assert($i >= 0 && $j >= 0 && $i != $j);
            $this->nvertex = max($this->nvertex, $i + 1, $j + 1);
        }

        $weights = [];
        foreach ($this->edges as $edge) {
            list($i, $j, $wt) = $edge;
            $weights[] = $wt;
        }
        $maxweight = max(0, max($weights));

        $this->endpoint = [];
        $arrayRange = range(0, 2 * $this->nedge - 1);
        foreach ($arrayRange as $p) {
            $this->endpoint[] = $this->edges[$this->floorintdiv($p, 2)][$p % 2];
        }

        $this->neighbend = [];
        for ($index = 0; $index < $this->nvertex; $index++) {
            $this->neighbend[] = [];
        }
        $arrayRange = range(0, count($this->edges) - 1);
        foreach ($arrayRange as $k) {
            list($i, $j, $w) = $this->edges[$k];
            $this->neighbend[$i][] = 2 * $k + 1;
            $this->neighbend[$j][] = 2 * $k;
        }

        $this->mate = array_fill(0, $this->nvertex, -1);
        $this->label = array_fill(0, 2 * $this->nvertex, 0);
        $this->labelend = array_fill(0, 2 * $this->nvertex, -1);
        $this->inblossom = range(0, $this->nvertex - 1);
        $this->blossomparent = array_fill(0, 2 * $this->nvertex, -1);
        $this->blossomchilds = array_fill(0, 2 * $this->nvertex, null);
        $this->blossombase = array_merge(range(0, $this->nvertex - 1), array_fill(0, $this->nvertex, -1));
        $this->blossomendps = array_fill(0, 2 * $this->nvertex, null);
        $this->bestedge = array_fill(0, 2 * $this->nvertex, -1);
        $this->blossombestedges = array_fill(0, 2 * $this->nvertex, null);
        $this->unusedblossoms = range($this->nvertex, 2 * $this->nvertex - 1);
        $this->dualvar = array_merge(array_fill(0, $this->nvertex, $maxweight), array_fill(0, $this->nvertex, 0));
        $this->allowedge = array_fill(0, $this->nedge, false);
        $this->queue = [];
    }

    public function slack($k_edge)
    {
        list($i_index, $j_index, $weight) = $this->safe_access($this->edges, $k_edge);

        return $this->dualvar[$i_index] + $this->dualvar[$j_index] - 2 * $weight;
    }

    public function blossomLeaves($b)
    {
        if ($b < $this->nvertex) {
            yield $b;
        } else {
            foreach ($this->blossomchilds[$b] as $t) {
                if ($t < $this->nvertex) {
                    yield $t;
                } else {
                    foreach ($this->blossomLeaves($t) as $v) {
                        yield $v;
                    }
                }
            }
        }
    }

    public function assignLabel($w, $t, $p)
    {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("assignLabel($w,$t,$p)");
        }
        $b = $this->inblossom[$w];
        assert($this->label[$w] == 0 && $this->label[$b] == 0);
        $this->label[$w] = $this->label[$b] = $t;
        $this->labelend[$w] = $this->labelend[$b] = $p;
        $this->bestedge[$w] = $this->bestedge[$b] = -1;
        if ($t == 1) {
            // $b became an S-vertex/blossom; add it(s vertices) to the queue.
            foreach ($this->blossomLeaves($b) as $leaf) {
                $this->queue[] = $leaf;
                if ($DEBUG) {
                    $DEBUG("PUSH $leaf");
                }
            }
        } elseif ($t == 2) {
            $base = $this->blossombase[$b];
            assert($this->mate[$base] >= 0);
            $this->assignLabel($this->endpoint[$this->mate[$base]], 1, $this->mate[$base] ^ 1);
        }
    }

    public function scanBlossom($v, $w)
    {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("scanBlossom($v,$w)");
        }
        $path = [];
        $base = -1;
        while ($v != -1 || $w != -1) {
            $b = $this->inblossom[$v];
            if ($this->label[$b] & 4) {
                $base = $this->blossombase[$b];
                break;
            }
            assert($this->label[$b] == 1);
            $path[] = $b;
            $this->label[$b] = 5;
            // Trace one step back.
            assert($this->labelend[$b] == $this->mate[$this->blossombase[$b]]);
            if ($this->labelend[$b] == -1) {
                $v = -1;
            } else {
                $v = $this->endpoint[$this->labelend[$b]];
                $b = $this->inblossom[$v];
                assert($this->label[$b] == 2);
                assert($this->labelend[$b] >= 0);
                $v = $this->endpoint[$this->labelend[$b]];
            }
            if ($w != -1) {
                list($v, $w) = [$w, $v];
            }
        }
        foreach ($path as $b) {
            $this->label[$b] = 1;
        }

        return $base;
    }

    public function addBlossom($base, $k)
    {
        global $DEBUG;
        list($v, $w, $wt) = $this->edges[$k];
        $bb = $this->inblossom[$base];
        $bv = $this->inblossom[$v];
        $bw = $this->inblossom[$w];
        $b = array_pop($this->unusedblossoms);
        if ($DEBUG) {
            $DEBUG("addBlossom($base,$k) (v=$v w=$w) -> $b");
        }
        $this->blossombase[$b] = $base;
        $this->blossomparent[$b] = -1;
        $this->blossomparent[$bb] = $b;
        $this->blossomchilds[$b] = [];
        $this->blossomendps[$b] = [];
        while ($bv != $bb) {
            $this->blossomparent[$bv] = $b;
            $this->blossomchilds[$b][] = $bv;
            $this->blossomendps[$b][] = $this->labelend[$bv];
            assert($this->label[$bv] == 2 ||
                    ($this->label[$bv] == 1 && $this->labelend[$bv] == $this->mate[$this->blossombase[$bv]]));
            assert($this->labelend[$bv] >= 0);
            $v = $this->endpoint[$this->labelend[$bv]];
            $bv = $this->inblossom[$v];
        }
        $this->blossomchilds[$b][] = $bb;
        $this->blossomchilds[$b] = array_reverse($this->blossomchilds[$b]);
        $this->blossomendps[$b] = array_reverse($this->blossomendps[$b]);
        $this->blossomendps[$b][] = 2 * $k;
        while ($bw != $bb) {
            $this->blossomparent[$bw] = $b;
            $this->blossomchilds[$b][] = $bw;
            $this->blossomendps[$b][] = $this->labelend[$bw] ^ 1;
            assert($this->label[$bw] == 2 ||
                   ($this->label[$bw] == 1 && $this->labelend[$bw] == $this->mate[$this->blossombase[$bw]]));
            assert($this->labelend[$bw] >= 0);
            $w = $this->endpoint[$this->labelend[$bw]];
            $bw = $this->inblossom[$w];
        }
        assert($this->label[$bb] == 1);
        $this->label[$b] = 1;
        $this->labelend[$b] = $this->labelend[$bb];
        $this->dualvar[$b] = 0;
        foreach ($this->blossomLeaves($b) as $v) {
            if ($this->label[$this->inblossom[$v]] == 2) {
                $this->queue[] = $v;
            }
            $this->inblossom[$v] = $b;
        }
        $this->bestedgeto = array_fill(0, 2 * $this->nvertex, -1);
        foreach ($this->blossomchilds[$b] as $bv) {
            if ($this->blossombestedges[$bv] === null) {
                $nblists = [];
                foreach ($this->blossomLeaves($bv) as $v) {
                    $nblist = [];
                    foreach ($this->neighbend[$v] as $p) {
                        $nblist[] = $this->floorintdiv($p, 2);
                    }
                    $nblists[] = $nblist;
                }
            } else {
                $nblists = [$this->blossombestedges[$bv]];
            }
            foreach ($nblists as $nblist) {
                foreach ($nblist as $k) {
                    list($i, $j, $wt) = $this->edges[$k];
                    if ($this->inblossom[$j] == $b) {
                        list($i, $j) = [$j, $i];
                    }
                    $bj = $this->inblossom[$j];
                    if (
                        $bj != $b && $this->label[$bj] == 1 &&
                        ($this->bestedgeto[$bj] == -1 ||
                        $this->slack($k) < $this->slack($this->bestedgeto[$bj]))
                    ) {
                        $this->bestedgeto[$bj] = $k;
                    }
                }
            }
            $this->blossombestedges[$bv] = null;
            $this->bestedge[$bv] = -1;
        }
        $this->blossombestedges[$b] = [];
        foreach ($this->bestedgeto as $k) {
            if ($k != -1) {
                $this->blossombestedges[$b][] = $k;
            }
        }

        $this->bestedge[$b] = -1;
        foreach ($this->blossombestedges[$b] as $k) {
            if ($this->bestedge[$b] == -1 || $this->slack($k) < $this->slack($this->bestedge[$b])) {
                $this->bestedge[$b] = $k;
            }
        }
        if ($DEBUG) {
            $DEBUG("blossomchilds[$b]=" . $this->arr_repr($this->blossomchilds[$b]));
        }
    }

    public function expandBlossom($b, $endstage)
    {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("expandBlossom($b,$endstage) " . $this->arr_repr($this->blossomchilds[$b]));
        }
        foreach ($this->blossomchilds[$b] as $s) {
            $this->blossomparent[$s] = -1;
            if ($s < $this->nvertex) {
                $this->inblossom[$s] = $s;
            } elseif ($endstage && $this->dualvar[$s] == 0) {
                $this->expandBlossom($s, $endstage);
            } else {
                foreach ($this->blossomLeaves($s) as $v) {
                    $this->inblossom[$v] = $s;
                }
            }
        }
        if ((!$endstage) && $this->label[$b] == 2) {
            assert($this->labelend[$b] >= 0);
            $entrychild = $this->inblossom[$this->endpoint[$this->labelend[$b] ^ 1]];
            $j = array_search($entrychild, $this->blossomchilds[$b]);
            if ($j & 1) {
                $j -= count($this->blossomchilds[$b]);
                $jstep = 1;
                $endptrick = 0;
            } else {
                $jstep = -1;
                $endptrick = 1;
            }
            $p = $this->labelend[$b];
            while ($j != 0) {
                $this->label[$this->endpoint[$p ^ 1]] = 0;
                $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
                $this->label[$this->endpoint[$this->blossomendps[$b][$safe_index] ^ $endptrick ^ 1]] = 0;
                $this->assignLabel($this->endpoint[$p ^ 1], 2, $p);
                $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
                $this->allowedge[$this->floorintdiv($this->blossomendps[$b][$safe_index], 2)] = true;
                $j += $jstep;
                $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
                $p = $this->blossomendps[$b][$safe_index] ^ $endptrick;
                $this->allowedge[$this->floorintdiv($p, 2)] = true;
                $j += $jstep;
            }
            $bv = $this->blossomchilds[$b][$j];
            $this->label[$this->endpoint[$p ^ 1]] = $this->label[$bv] = 2;
            $this->labelend[$this->endpoint[$p ^ 1]] = $this->labelend[$bv] = $p;
            $this->bestedge[$bv] = -1;
            $j += $jstep;
            while ($this->safe_access($this->blossomchilds[$b], $j) != $entrychild) {
                $bv = $this->safe_access($this->blossomchilds[$b], $j);
                if ($this->label[$bv] == 1) {
                    $j += $jstep;
                    continue;
                }
                foreach ($this->blossomLeaves($bv) as $v) {
                    if ($this->label[$v] != 0) {
                        break;
                    }
                }
                if ($this->label[$v] != 0) {
                    assert($this->label[$v] == 2);
                    assert($this->inblossom[$v] == $bv);
                    $this->label[$v] = 0;
                    $this->label[$this->endpoint[$this->mate[$this->blossombase[$bv]]]] = 0;
                    $this->assignLabel($v, 2, $this->labelend[$v]);
                }
                $j += $jstep;
            }
        }
        $this->label[$b] = $this->labelend[$b] = -1;
        $this->blossomchilds[$b] = $this->blossomendps[$b] = null;
        $this->blossombase[$b] = -1;
        $this->blossombestedges[$b] = null;
        $this->bestedge[$b] = -1;
        $this->unusedblossoms[] = $b;
    }

    public function augmentBlossom($b, $v)
    {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("augmentBlossom($b,$v)");
        }
        $t = $v;
        while ($this->blossomparent[$t] != $b) {
            $t = $this->blossomparent[$t];
        }
        if ($t >= $this->nvertex) {
            $this->augmentBlossom($t, $v);
        }
        $i = $j = array_search($t, $this->blossomchilds[$b]);
        if ($i & 1) {
            $j -= count($this->blossomchilds[$b]);
            $jstep = 1;
            $endptrick = 0;
        } else {
            $jstep = -1;
            $endptrick = 1;
        }
        while ($j != 0) {
            $j += $jstep;
            $safe_index = $this->safe_index($this->blossomchilds[$b], $j);
            $t = $this->blossomchilds[$b][$safe_index];
            $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
            $p = $this->blossomendps[$b][$safe_index] ^ $endptrick;
            if ($t >= $this->nvertex) {
                $this->augmentBlossom($t, $this->endpoint[$p]);
            }
            $j += $jstep;
            $safe_index = $this->safe_index($this->blossomchilds[$b], $j);
            $t = $this->blossomchilds[$b][$safe_index];
            if ($t >= $this->nvertex) {
                $this->augmentBlossom($t, $this->endpoint[$p ^ 1]);
            }
            $this->mate[$this->endpoint[$p]] = $p ^ 1;
            $this->mate[$this->endpoint[$p ^ 1]] = $p;
            if ($DEBUG) {
                $DEBUG('PAIR(a) ' . $this->endpoint[$p] . ' ' . $this->endpoint[$p ^ 1] . ' (k=' . $this->floorintdiv($p, 2) . ')');
            }
        }
        $this->blossomchilds[$b] = array_merge(array_slice($this->blossomchilds[$b], $i), array_slice($this->blossomchilds[$b], 0, $i));
        $this->blossomendps[$b] = array_merge(array_slice($this->blossomendps[$b], $i), array_slice($this->blossomendps[$b], 0, $i));
        $this->blossombase[$b] = $this->blossombase[$this->blossomchilds[$b][0]];
        assert($this->blossombase[$b] == $v);
    }

    public function augmentMatching($k)
    {
        global $DEBUG;
        list($v, $w, $wt) = $this->edges[$k];
        if ($DEBUG) {
            $DEBUG("augmentMatching($k) (v=$v w=$w)");
        }
        if ($DEBUG) {
            $DEBUG("PAIR(b) $v $w (k=$k)");
        }
        foreach ([[$v, 2 * $k + 1], [$w, 2 * $k]] as $row) {
            list($s, $p) = $row;
            while (1) {
                $bs = $this->inblossom[$s];
                assert($this->label[$bs] == 1);
                assert($this->labelend[$bs] == $this->mate[$this->blossombase[$bs]]);
                if ($bs >= $this->nvertex) {
                    $this->augmentBlossom($bs, $s);
                }
                $this->mate[$s] = $p;
                if ($this->labelend[$bs] == -1) {
                    break;
                }
                $t = $this->endpoint[$this->labelend[$bs]];
                $bt = $this->inblossom[$t];
                assert($this->label[$bt] == 2);
                assert($this->labelend[$bt] >= 0);
                $s = $this->endpoint[$this->labelend[$bt]];
                $j = $this->endpoint[$this->labelend[$bt] ^ 1];
                assert($this->blossombase[$bt] == $t);
                if ($bt >= $this->nvertex) {
                    $this->augmentBlossom($bt, $j);
                }
                $this->mate[$j] = $this->labelend[$bt];
                $p = $this->labelend[$bt] ^ 1;
                if ($DEBUG) {
                    $DEBUG("PAIR(c) $s $t (k=" . $this->floorintdiv($p, 2) . ')');
                }
            }
        }
    }

    public function verifyOptimum()
    {
        if ($this->maxcardinality) {
            $vdualoffset = max(0, -min(array_slice($this->dualvar, 0, $this->nvertex)));
        } else {
            $vdualoffset = 0;
        }
        assert(min(array_slice($this->dualvar, 0, $this->nvertex)) + $vdualoffset >= 0);
        assert(min(array_slice($this->dualvar, $this->nvertex)) >= 0);
        $arrayRange = range(0, $this->nedge - 1);
        foreach ($arrayRange as $k) {
            list($i, $j, $wt) = $this->edges[$k];
            $s = $this->dualvar[$i] + $this->dualvar[$j] - 2 * $wt;
            $iblossoms = [$i];
            $jblossoms = [$j];
            while ($this->blossomparent[$this->last_elem($iblossoms)] != -1) {
                $iblossoms[] = $this->blossomparent[$this->last_elem($iblossoms)];
            }
            while ($this->blossomparent[$this->last_elem($jblossoms)] != -1) {
                $jblossoms[] = $this->blossomparent[$this->last_elem($jblossoms)];
            }
            $iblossoms = array_reverse($iblossoms);
            $jblossoms = array_reverse($jblossoms);
            $arrayMap = array_map(null, $iblossoms, $jblossoms);
            foreach ($arrayMap as $row) {
                list($bi, $bj) = $row;
                if ($bi != $bj) {
                    break;
                }
                $s += 2 * $this->dualvar[$bi];
            }
            assert($s >= 0);
            if ($this->floorintdiv($this->mate[$i], 2) == $k || $this->floorintdiv($this->mate[$j], 2) == $k) {
                assert($this->floorintdiv($this->mate[$i], 2) == $k && $this->floorintdiv($this->mate[$j], 2) == $k);
                assert($s == 0);
            }
        }
        $arrayRange = range(0, $this->nvertex - 1);
        foreach ($arrayRange as $v) {
            assert($this->mate[$v] >= 0 || $this->dualvar[$v] + $vdualoffset == 0);
        }
        $arrayRange = range($this->nvertex, 2 * $this->nvertex - 1);
        foreach ($arrayRange as $b) {
            if ($this->blossombase[$b] >= 0 && $this->dualvar[$b] > 0) {
                assert(count($this->blossomendps[$b]) % 2 == 1);
                foreach (array_slice($this->blossomendps[$b], 1, 1) as $p) {
                    assert($this->mate[$this->endpoint[$p]] == $p ^ 1);
                    assert($this->mate[$this->endpoint[$p ^ 1]] == $p);
                }
            }
        }
    }

    public function checkDelta2()
    {
        global $DEBUG;
        $arrayRange = range(0, $this->nvertex - 1);
        foreach ($arrayRange as $v) {
            if ($this->label[$this->inblossom[$v]] == 0) {
                $bd = null;
                $bk = -1;
                foreach ($this->neighbend[$v] as $p) {
                    $k = $this->floorintdiv($p, 2);
                    $w = $this->endpoint[$p];
                    if ($this->label[$this->inblossom[$w]] == 1) {
                        $d = $this->slack($k);
                        if ($bk == -1 || $d < $bd) {
                            $bk = $k;
                            $bd = $d;
                        }
                    }
                }
                if ($DEBUG && ($this->bestedge[$v] != -1 || $bk != -1) && ($this->bestedge[$v] == -1 || $bd != $this->slack($this->bestedge[$v]))) {
                    $DEBUG('v=' . $v . ' bk=' . $bk . ' bd=' . $bd . ' $this->bestedge[$v]=' . $this->bestedge[$v] . ' slack=' . $this->slack($this->bestedge[$v]));
                }
                assert(($bk == -1 && $this->bestedge[$v] == -1) || ($this->bestedge[$v] != -1 && $bd == $this->slack($this->bestedge[$v])));
            }
        }
    }

    public function checkDelta3()
    {
        global $DEBUG;
        $bk = -1;
        $bd = null;
        $tbk = -1;
        $tbd = null;
        $arrayRange = range(0, 2 * $this->nvertex - 1);
        foreach ($arrayRange as $b) {
            if ($this->blossomparent[$b] == -1 && $this->label[$b] == 1) {
                foreach ($this->blossomLeaves($b) as $v) {
                    foreach ($this->neighbend[$v] as $p) {
                        $k = $this->floorintdiv($p, 2);
                        $w = $this->endpoint[$p];
                        if ($this->inblossom[$w] != $b && $this->label[$this->inblossom[$w]] == 1) {
                            $d = $this->slack($k);
                            if ($bk == -1 || $d < $bd) {
                                $bk = $k;
                                $bd = $d;
                            }
                        }
                    }
                }
                if ($this->bestedge[$b] != -1) {
                    list($i, $j, $wt) = $this->edges[$this->bestedge[$b]];
                    assert($this->inblossom[$i] == $b || $this->inblossom[$j] == $b);
                    assert($this->inblossom[$i] != $b || $this->inblossom[$j] != $b);
                    assert($this->label[$this->inblossom[$i]] == 1 && $this->label[$this->inblossom[$j]] == 1);
                    if ($tbk == -1 || $this->slack($this->bestedge[$b]) < $tbd) {
                        $tbk = $this->bestedge[$b];
                        $tbd = $this->slack($this->bestedge[$b]);
                    }
                }
            }
        }
        if ($DEBUG && $bd != $tbd) {
            $DEBUG("bk=$bk tbk=$tbk bd=" . $this->arr_repr($bd) . ' tbd=' . $this->arr_repr($tbd));
        }
        assert($bd == $tbd);
    }

    public function last_elem($arr)
    {
        $values = array_values($arr);

        return end($values);
    }

    public function floorintdiv($x, $y)
    {
        return intval(floor($x / $y));
    }

    public function safe_index($arr, $requested_index)
    {
        if ($requested_index >= 0) {
            return $requested_index;
        }

        return count($arr) + $requested_index;
    }

    public function safe_access($arr, $requested_index)
    {
        return $arr[$this->safe_index($arr, $requested_index)];
    }

    public function arr_repr($arr)
    {
        $s = '[';
        foreach ($arr as $v) {
            if (is_array($v)) {
                $s .= $this->arr_repr($v);
            } else {
                $s .= "$v, ";
            }
        }

        return rtrim($s, ', ') . ']';
    }

    public function print_arr($arr)
    {
        echo $this->arr_repr($arr) . "\n";
    }

    public function main()
    {
        global $DEBUG;
        global $CHECK_DELTA;
        global $CHECK_OPTIMUM;

        if (!$this->edges) {
            return [];
        }

        $arrayRange = range(0, $this->nvertex - 1);
        foreach ($arrayRange as $t) {
            if ($DEBUG) {
                $DEBUG("STAGE $t");
            }

            $this->label = array_fill(0, 2 * $this->nvertex, 0);

            $this->bestedge = array_fill(0, 2 * $this->nvertex, -1);
            for ($i = $this->nvertex; $i < count($this->blossombestedges); $i++) {
                $this->blossombestedges[$i] = null;
            }

            $this->allowedge = array_fill(0, $this->nedge, false);

            $this->queue = [];

            foreach ($arrayRange as $v) {
                if ($this->mate[$v] == -1 && $this->label[$this->inblossom[$v]] == 0) {
                    $this->assignLabel($v, 1, -1);
                }
            }

            $augmented = 0;
            while (1) {
                if ($DEBUG) {
                    $DEBUG('SUBSTAGE');
                }

                while ($this->queue && !$augmented) {
                    $v = array_pop($this->queue);
                    if ($DEBUG) {
                        $DEBUG("POP v=$v");
                    }
                    assert($this->label[$this->inblossom[$v]] == 1);

                    foreach ($this->neighbend[$v] as $p) {
                        $k = $this->floorintdiv($p, 2);
                        $w = $this->endpoint[$p];
                        if ($this->inblossom[$v] == $this->inblossom[$w]) {
                            continue;
                        }
                        if (!$this->allowedge[$k]) {
                            $kslack = $this->slack($k);
                            if ($kslack <= 0) {
                                $this->allowedge[$k] = true;
                            }
                        }
                        if ($this->allowedge[$k]) {
                            if ($this->label[$this->inblossom[$w]] == 0) {
                                $this->assignLabel($w, 2, $p ^ 1);
                            } elseif ($this->label[$this->inblossom[$w]] == 1) {
                                $base = $this->scanBlossom($v, $w);
                                if ($base >= 0) {
                                    $this->addBlossom($base, $k);
                                } else {
                                    $this->augmentMatching($k);
                                    $augmented = 1;
                                    break;
                                }
                            } elseif ($this->label[$w] == 0) {
                                assert($this->label[$this->inblossom[$w]] == 2);
                                $this->label[$w] = 2;
                                $this->labelend[$w] = $p ^ 1;
                            }
                        } elseif ($this->label[$this->inblossom[$w]] == 1) {
                            $b = $this->inblossom[$v];
                            if ($this->bestedge[$b] == -1 || $kslack < $this->slack($this->bestedge[$b])) {
                                $this->bestedge[$b] = $k;
                            }
                        } elseif ($this->label[$w] == 0) {
                            if ($this->bestedge[$w] == -1 || $kslack < $this->slack($this->bestedge[$w])) {
                                $this->bestedge[$w] = $k;
                            }
                        }
                    }
                }

                if ($augmented) {
                    break;
                }

                $deltatype = -1;
                $delta = $deltaedge = $deltablossom = null;

                if ($CHECK_DELTA) {
                    $this->checkDelta2();
                    $this->checkDelta3();
                }

                if (!$this->maxcardinality) {
                    $deltatype = 1;
                    $delta = min(array_slice($this->dualvar, 0, $this->nvertex));
                }

                foreach ($arrayRange as $v) {
                    if ($this->label[$this->inblossom[$v]] == 0 && $this->bestedge[$v] != -1) {
                        $d = $this->slack($this->bestedge[$v]);
                        if ($deltatype == -1 || $d < $delta) {
                            $delta = $d;
                            $deltatype = 2;
                            $deltaedge = $this->bestedge[$v];
                        }
                    }
                }

                $arrayRange2 = range(0, 2 * $this->nvertex - 1);
                foreach ($arrayRange2 as $b) {
                    if ($this->blossomparent[$b] == -1 && $this->label[$b] == 1 && $this->bestedge[$b] != -1) {
                        $kslack = $this->slack($this->bestedge[$b]);
                        if ((int) $kslack == $kslack) {
                            assert(($kslack % 2) == 0);
                            $d = $this->floorintdiv($kslack, 2);
                        } else {
                            $d = $kslack / 2; // ORIGINALLY SINGLE SLASH DIVISION IN PYTHON SO NOT CONVERTED TO INTDIV
                        }
                        if ($deltatype == -1 || $d < $delta) {
                            $delta = $d;
                            $deltatype = 3;
                            $deltaedge = $this->bestedge[$b];
                        }
                    }
                }

                $arrayRange3 = range($this->nvertex, 2 * $this->nvertex - 1);
                foreach ($arrayRange3 as $b) {
                    if (
                        $this->blossombase[$b] >= 0 && $this->blossomparent[$b] == -1 &&
                            $this->label[$b] == 2 &&
                            ($deltatype == -1 || $this->dualvar[$b] < $delta)
                    ) {
                        $delta = $this->dualvar[$b];
                        $deltatype = 4;
                        $deltablossom = $b;
                    }
                }

                if ($deltatype == -1) {
                    assert($this->maxcardinality);
                    $deltatype = 1;
                    $delta = max(0, min(array_slice($this->dualvar, 0, $this->nvertex)));
                }

                foreach ($arrayRange as $v) {
                    if ($this->label[$this->inblossom[$v]] == 1) {
                        $this->dualvar[$v] -= $delta;
                    } elseif ($this->label[$this->inblossom[$v]] == 2) {
                        $this->dualvar[$v] += $delta;
                    }
                }
                foreach ($arrayRange3 as $b) {
                    if ($this->blossombase[$b] >= 0 && $this->blossomparent[$b] == -1) {
                        if ($this->label[$b] == 1) {
                            $this->dualvar[$b] += $delta;
                        } elseif ($this->label[$b] == 2) {
                            $this->dualvar[$b] -= $delta;
                        }
                    }
                }

                if ($DEBUG) {
                    $DEBUG("delta$deltatype=$delta");
                }
                if ($deltatype == 1) {
                    break;
                } elseif ($deltatype == 2) {
                    $this->allowedge[$deltaedge] = true;
                    list($i, $j, $wt) = $this->edges[$deltaedge];
                    if ($this->label[$this->inblossom[$i]] == 0) {
                        list($i, $j) = [$j, $i];
                    }
                    assert($this->label[$this->inblossom[$i]] == 1);
                    $this->queue[] = $i;
                } elseif ($deltatype == 3) {
                    $this->allowedge[$deltaedge] = true;
                    list($i, $j, $wt) = $this->edges[$deltaedge];
                    assert($this->label[$this->inblossom[$i]] == 1);
                    $this->queue[] = $i;
                } elseif ($deltatype == 4) {
                    $this->expandBlossom($deltablossom, false);
                }
            }

            if (!$augmented) {
                break;
            }

            $arrayRange2 = range($this->nvertex, 2 * $this->nvertex - 1);
            foreach ($arrayRange2 as $b) {
                if (
                    $this->blossomparent[$b] == -1 && $this->blossombase[$b] >= 0 &&
                        $this->label[$b] == 1 && $this->dualvar[$b] == 0
                ) {
                    $this->expandBlossom($b, true);
                }
            }
        }

        if ($CHECK_OPTIMUM) {
            $this->verifyOptimum();
        }

        foreach ($arrayRange as $v) {
            if ($this->mate[$v] >= 0) {
                $this->mate[$v] = $this->endpoint[$this->mate[$v]];
            }
        }
        foreach ($arrayRange as $v) {
            assert($this->mate[$v] == -1 || $this->mate[$this->mate[$v]] == $v);
        }

        return $this->mate;
    }
}
