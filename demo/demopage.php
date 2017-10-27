        <div id="switcher">
            <div class="center">
                <div class="logo">
                <?php 
                    echo '<a href="'.$boardurl.'" title="Theme Demo">
                        <img src="images/logo.png" alt="Theme Demo logo" />
                    </a>';
                ?>
                </div>

                <ul>
                    <li id="theme_list"><a id="theme_select" href="#"><?php echo !empty($_REQUEST['tema']) ? $_REQUEST['tema'] : $row2['title'];  ?></a>
                        <ul>
                        <?php   foreach($context['temalar'] as $i => $tema){
                        echo '<li><a href="'.$demoyolurl.'index.php?tema='.$tema['title'].'">'.$tema['title'].'<span>'.$tema['catname'].'</span></a><img alt="'.$tema['title'].'" style="width:300px;height:auto;" class="preview" src="',$tema['pictureurl'] != '' ? $tema['pictureurl'] : $modSettings['tema_url'].'temaresim/'.$tema['picture'],'" /></li>';
                        }

                        ?>

                        </ul>
                    </li>   
                </ul>

                <div class="responsive">
                    <a href="#" class="desktop active" title="<?php echo $txt['desktop']; ?>"></a> 
                    <a href="#" class="tabletlandscape" title="<?php echo $txt['tabletlandscape']; ?>"></a> 
                    <a href="#" class="tabletportrait" title="<?php echo $txt['tabletportrait']; ?>"></a> 
                    <a href="#" class="mobilelandscape" title="<?php echo $txt['mobilelandscape']; ?>"></a>
                    <a href="#" class="mobileportrait" title="<?php echo $txt['mobileportrait']; ?>"></a>
                </div>
        <?php
        if(isset($_REQUEST['tema'])){
            $temaisim = htmlspecialchars($_REQUEST['tema'],ENT_QUOTES);
            $dbresult1 = $smcFunc['db_query']('', "SELECT p.title, p.ID_FILE, p.demourl FROM {db_prefix}tema_file as p WHERE p.title = '$temaisim' order by p.title desc limit 1");
            $row1 = $smcFunc['db_fetch_assoc']($dbresult1);
            $smcFunc['db_free_result']($dbresult1);
			$al=$row1['demourl'];
			$al=str_replace ("https://","//",$al);
			$al=str_replace ("http://","//",$al);

        echo'<ul class="links">
                        <li class="purchase" rel="'.$temayol.$row1['ID_FILE'].'">
                            <a href="'.$temayol.$row1['ID_FILE'].'"><img src="images/purchase.png" alt="'.$txt['purchade'].'" />'.$txt['purchade'].'</a>
                        </li>
                    
                                            <li class="close" rel="'.$al.'">
                            <a href="'.$al.'"><img src="images/cross.png" alt="Kapat" />'.$txt['clos'].'</a>
                        </li>     
                                    </ul>

                                    <div class="share">
                        <ul>
                            <li><div class="g-plusone" data-size="medium" data-href="'.$temayol.$row1['ID_FILE'].'"></div></li>
                        </ul>
                    </div>
                                <div style="clear:both"></div>
            </div>
        </div>';


            echo '<iframe id="iframe" src="'.$al.'" frameborder="0" width="100%" height="100%"></iframe>';
        }else{
            echo '
                    </div>
                                <div style="clear:both"></div>
            </div>
        </div>';

            echo '<iframe id="iframe" src="'.$al.'" frameborder="0" width="100%" height="100%"></iframe>';
        }
        ?>