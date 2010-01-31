<?php
/**
 * rep2expack - tGrep �������ʂ̃����_�����O for iPhone/Ajax
 */

if ($htm['query'] === '') {
    echo "<div class=\"panel\" title=\"�X���b�h����\">�����ȃL�[���[�h�ł��B</div>";
    return;
} elseif (!is_array($threads) || count($threads) < 1) {
    echo "<div class=\"panel\" title=\"{$htm['query']} (�X��)\">&quot;{$htm['query']}&quot;",
            " �Ƀ}�b�`����X���b�h�͂���܂���ł����B</div>";
    return;
}

$ix_base_url = sprintf('tgrepc.php?%s%s&amp;iq=%s&amp;M=%d',
                       $_conf['detect_hint_q'],
                       $_conf['k_at_a'],
                       $htm['query_en'],
                       time()
                       );

if ($htm['board'] && isset($profile['boards'][$htm['board']])) {
    $group_name = $profile['boards'][$htm['board']]->name;
} elseif ($htm['category'] && isset($profile['categories'][$htm['category']])) {
    $group_name = $profile['categories'][$htm['category']]->name;
} elseif (isset($_GET['ic'])) {
    $group_name = '�i��';
} else {
    $group_name = '�X��';
}

echo "<ul class=\"tgrep-result\" title=\"{$htm['query']} ({$group_name})\">";

if (isset($_GET['ic'])) {
    // �i�荞�݃��j���[
    $cid = (int)$_GET['ic'];

    if ($cid == 0) {
        foreach ($profile['categories'] as $category) {
            printf('<li><a href="%s&amp;ic=%d">%s ' .
                        '<span class="size-l">(%d)</span></a></li>',
                   $ix_base_url,
                   $category->id,
                   $category->name,
                   $category->hits
                   );
        }
    } elseif (!isset($profile['categories'][$cid])) {
        '<li class="color-r">Not Found</li>';
    } else {
        $category = $profile['categories'][$cid];

        printf('<li><a href="%s&amp;C=%d" class="align-c">%s' .
                    ' <span class="size-l">(%d)</span></a></li>',
               $ix_base_url,
               $category->id,
               htmlspecialchars($category->name, ENT_QUOTES),
               $category->hits
               );

        $boards = array();

        foreach ($category->member as $bid) {
            if (isset($profile['boards'][$bid])) {
                $boards[] = $profile['boards'][$bid];
            }
        }

        usort($boards,
              create_function('$a, $b',
                              'if (($c = $b->hits - $a->hits) != 0) {
                                   return $c;
                               }
                               return strcasecmp($a->name, $b->name);'
                               )
              );

        foreach ($boards as $board) {
            printf('<li><a href="%s&amp;ib=%d&amp;S=%s&amp;B=%s">%s' .
                        ' <span class="size-l">(%d)</span></a></li>',
                   $ix_base_url,
                   $board->id,
                   rawurlencode($board->site),
                   rawurlencode($board->bbs),
                   htmlspecialchars($board->name, ENT_QUOTES),
                   $board->hits
                   );
        }
    }

} else {
    // �}�b�`�����X���b�h��\��
    $current_page = (isset($_GET['P'])) ? (int)$_GET['P'] : 1;
    $all_pages = ceil($subhits / $limit);

    $disp_range = sprintf('<li class="group">%d-%d/%s</li>',
                          1 + $limit * ($current_page - 1),
                          min($limit * $current_page, $subhits),
                          $htm['hits']
                          );

    if ($current_page == 1 && $htm['category'] == 0 && $htm['board'] == 0) {
        echo "<li><a href=\"{$ix_base_url}&amp;ic=0\" class=\"align-r\">�i�荞��</a></li>";
    }

    // �\���͈�
    echo $disp_range;

    foreach ($threads as $o => $t) {
        $ttitle_ht = strip_tags($t->title);
        $host_en = rawurlencode($t->host);
        $bbs_en = rawurlencode($t->bbs);

        $turl = sprintf('%s?host=%s&amp;bbs=%s&amp;key=%d&amp;ttitle_en=%s',
                        $_conf['read_php'],
                        $host_en,
                        $bbs_en,
                        $t->tkey,
                        UrlSafeBase64::encode($ttitle_ht)
                        );

        /*
        $burl = sprintf('%s?host=%s&amp;bbs=%s&amp;itaj_en=%s&amp;word=%s',
                        $_conf['subject_php'],
                        $host_en,
                        $bbs_en,
                        UrlSafeBase64::encode($t->ita),
                        $htm['query_en']
                        );
        */

        printf('<li><a href="%s" target="_self">%s<br />' .
                    '<span class="size-m weight-n">%s %s</span></a></li>',
               $turl,
               $ttitle_ht,
               date('y/m/d ', $t->tkey),
               $t->ita
               );
    }

    //echo $disp_range;

    // ���̃y�[�W�ւ̃����N
    if ($subhits && $subhits > $limit && $all_pages > $current_page) {
        if ($htm['board']) {
            $ix_filter_query = sprintf('&amp;ib=%d&amp;S=%s&amp;B=%s',
                                       $htm['board'],
                                       $htm['site_en'],
                                       $htm['bbs_en']
                                       );
        } elseif ($htm['category']) {
            $ix_filter_query = sprintf('&amp;C=%d', $htm['category']);
        } else {
            $ix_filter_query = '';
        }

        printf('<li><a href="%s%s&amp;P=%d" class="align-r">����%d��</a></li>',
               $ix_base_url,
               $ix_filter_query,
               $current_page + 1,
               min($limit, $subhits - $limit * $current_page)
               );
    }
}

echo '</ul>';

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
