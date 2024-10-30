<div class="wrap">
    <h1>MasOffer Promotion</h1>

    <!-- using admin_action_ . $_REQUEST['action'] hook in admin.php -->
    <form action="<?= admin_url( 'admin.php' ); ?>" method="post">
        <input type="hidden" name="action" value="masoffer_promotion_action" />
        <?php wp_nonce_field( 'update-info-mo_' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="publisher_id">Publisher ID</label></th>
                <td> <input name="publisher_id" id="publisher_id" type="text" value="<?= $publisher_id ?>" class="regular-text code"></td>
            </tr>

            <tr>
                <th><label for="token">Publisher Token</label></th>
                <td> <input name="token" id="token" type="text" value="<?= $token ?>" class="regular-text code"></td>
            </tr>

            <tr>
                <th><label for="domain">Domain</label></th>
                <td>
                    <select name="domain" id="domain" class="regular-text code">
                        <?php foreach ($packing_domains as $packing_domain) { ?>
                            <option value="<?= $packing_domain ?>"
                                <?= $packing_domain == $domain ? 'selected': ''?>>
                                <?= $packing_domain ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="protocol">Protocol</label></th>
                <td>
                    <select name="protocol" id="protocol" class="regular-text code">
                        <option value="https"
                            <?= 'https' == $protocol ? 'selected': ''?>>
                            https
                        </option>
                        <option value="http"
                            <?= 'http' == $protocol ? 'selected': ''?>>
                            http
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="display">Display mode</label></th>
                <td>
                    <select name="display" id="display" class="regular-text code">
                        <option value="logo" <?= 'logo' == $display ? 'selected' : '' ?>>
                            Hiển thị logo
                        </option>
                        <option value="discount" <?= 'discount' == $display ? 'selected' : '' ?>>
                            Hiển thị discount
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="color">Color</label></th>
                <td> <input name="color" id="color" type="color" value="<?= $color ?>"></td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input class="button button-primary" type="submit" value="Save"/>
        </p>
        <div>
            <p>Tổng số offer trong hệ thống: <strong><?= count($offers) ?></strong></p>
            <p>Cập nhật lần cuối: <strong><?= $updated_at ?></strong></p>
            <p></p>
            <input class="button button-primary" type="submit" name="update-offer" value="Cập nhật Offer"/>
        </div>
    </form>
</div> <!-- end div.wrap -->
