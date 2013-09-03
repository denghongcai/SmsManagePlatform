<div id="main">
    <div id="leftside" style="height: 850px"    >
        <ul>
            <li><a href="#">用户管理</a></li>

            <ul id="next">
                <li><a href="<?= base_url('check/intercept') ?>">短信拦截</a></li>
                <li><a href="<?= base_url('check/uniquesms') ?>">唯一信息调取</a></li>
                <li><a href="<?= base_url('check/fishsms/') ?>">钓鱼信息查询</a></li>
                <li><a href="<?= base_url('check/output') ?>">待审核信息导出</a></li>
            </ul>


        </ul>
    </div>
    <div id="rightside" style="height: 850px">
        <p class="title-intro">用户管理&gt;&gt;短信审核</p>

        <form action='' method="post">
            <table>
                <tr>
                    <td width="143" class="blue">开始时间</td>
                    <td width="220">
                        <input type="text" name="startime" id="textfield">
                    </td>
                    <td width="189" class="blue">结束时间</td>
                    <td width="228">
                        <input type="text" name="endtime" id="textfield2">
                    </td>
                </tr>
                <tr>
                    <td class="blue">网关类型</td>
                    <td colspan="3"><label for="select"></label>
                        <select name="gatetype" class="input-length1" id="select">
                            <option value="0">全部</option>
                            <option value='1'>移动</option>
                            <option value="2">联通</option>
                            <option value="3">电信</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="blue">企业名称</td>
                    <td>
                        <input type="text" name="fname" id="textfield3">
                    </td>
                    <td class="blue">短信内容</td>
                    <td><label for="textfield4"></label>
                        <input type="text" name="smscontent" id="textfield4">
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <button type="button" class="button-s" value="<?= base_url('check/intercept/select') ?>">查询
                        </button>
                        <button type="button" class="button-s" value="<?= base_url('check/intercept/accept') ?>">通过
                        </button>
                        <button type="button" class="button-s" value="<?= base_url('check/intercept/reject') ?>">驳回
                        </button>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td class="blue">测试号码</td>
                    <td colspan="5"><input name="textfield3" type="text" class="input-length" id="textfield3"></td>
                </tr>
                <tr>
                    <td class="blue">外网短信内容更新</td>
                    <td colspan="5"><textarea name="" id="" cols="30" rows="10"></textarea>

                        <p>最多为140字，已输入<span class="number">0</span>字。<span class="red">注意：审核通过时，才会替换勾选的短信内容</span></p>
                        ;
                    </td>
                </tr>
                <tr>
                    <td colspan="6" class="blue">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="6"><span class="result">查询结果：共<span>0</span>条记录，当前<span>0/0</span>页</span>
                        <a href="#" class="button-s">首页</a>
                        <a href="#" class="button-s">上一页</a>
                        <a href="#" class="button-s">下一页</a>
                        <a href="#" class="button-s">尾页</a></td>
                </tr>
                <tr>
                    <td width="77" class="blue"><input type="checkbox" name="checkbox" id="checkbox">
                        <label for="checkbox">全选</label></td>
                    <td width="137" class="blue">企业名称</td>
                    <td width="124" class="blue">网关类型</td>
                    <td width="262" class="blue">短信内容</td>
                    <td width="89" class="blue">发送数量</td>
                    <td width="83" class="blue">发送时间</td>
                </tr>
                <?php if(isset($unchecksms)):?>
                    <?php foreach($unchecksms as $item):?>
                        <tr>
                            <td width="77">
                                <input type="checkbox" name="checkbox" id="checkbox">
                            </td>
                            <td width="137"><?=$item['fname']?></td>
                            <td width="124"><?=$item['gatetype']?></td>
                            <td width="262"><?=$item['content']?></td>
                            <td width="89"><?=$item['num']?></td>
                            <td width="83"><?=$item['addtime']?></td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <tr>
                    <td colspan="6">&nbsp;</td>
                </tr>

            </table>
        </form>
    </div>
</div>
</div>
<script type="text/javascript">
    $(function(){
        $('button.button-s').click(function(){
            $("form").attr('action', $(this).val());
            $("form").submit();
        })
    })
</script>
</body>
</html>