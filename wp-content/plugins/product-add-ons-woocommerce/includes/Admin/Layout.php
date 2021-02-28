<?php

namespace ZAddons\Admin;

use ZAddons\Addons;use ZAddons\Admin;
use ZAddons\Plugin;

class Layout
{
	public function __construct()
	{
		add_action('in_admin_header', [$this, 'pageHeader']);
	}

	public function pageHeader()
	{
		if (!self::isActive()) {
			return;
		} ?>
				<div class="zaddon-layout-wrapper">
						<div class="zaddon-layout">
								<div class="zaddon-base">
										<a href="https://www.bizswoop.com/wp/product-management" target="_blank">
												<img
													class="zaddon-logo"
													src="<?= Plugin::getUrl('assets/images/admin/logo.png'); ?>"
													alt="Product Add-ons"
												>
										</a>
										<div class="zaddon-title">
												<a href="https://www.bizswoop.com/wp/product-management" target="_blank">
														<?php _e('Product Add-ons', 'product-add-ons-woocommerce') ?>
												</a>
										</div>
										<div class="zaddon-slogan">
												<?php _e('Advanced Customizations', 'product-add-ons-woocommerce') ?>
										</div>
								</div>
								<div class="zaddon-navigation">
										<ul>
												<li>
														<a
															href="<?= Admin::getUrl('groups'); ?>"
															class="<?= self::isActiveClass('groups'); ?>"
														>
																<div class="zaddon-icon">
																		<i class="fal fa-th-large"></i>
																</div>
																<?php _e('Groups', 'product-add-ons-woocommerce') ?>
														</a>
												</li>
												<?php if (Addons::is_active_add_on(Addons::CUSTOMIZE_ADDON_NAMESPACE, true) || Addons::is_active_add_on(Addons::CHECKOUT_ADDON_NAMESPACE, true)) : ?>
												<li>
														<a
															href="<?= Admin::getUrl('settings'); ?>"
															class="<?= self::isActiveClass('settings'); ?>"
														>
																<div class="zaddon-icon">
																		<i class="fal fa-cog"></i>
																</div>
																<?php _e('Settings', 'product-add-ons-woocommerce') ?>
														</a>
												</li>
												<?php endif; ?>
												<li>
														<a
															href="<?= Admin::getUrl('add-ons'); ?>"
															class="<?= self::isActiveClass('add-ons'); ?>"
														>
																<div class="zaddon-icon">
																		<i class="far fa-cubes"></i>
																</div>
																<?php _e('Add-ons', 'product-add-ons-woocommerce') ?>
														</a>
												</li>
												<li>
														<a href="http://bizswoop.com/" target="_blank">
																<div class="zaddon-icon">
																		<img
																			src="<?= Plugin::getUrl('assets/images/admin/bizswoop.png'); ?>"
																			alt="BizSwoop">
																</div>
																BizSwoop
														</a>
												</li>
										</ul>
								</div>
						</div>
				</div>
				<?php
	}

	public static function isActive()
	{
			return \get_current_screen()->id === 'product_page_za_groups';
	}

	public static function isActiveClass($tag)
	{
			$is_active = isset($_GET['tab']) && $_GET['tab'] === $tag || !isset($_GET['tab']) && $tag === 'groups';
			return $is_active ? 'zaddon-active-link' : '';
	}
}
