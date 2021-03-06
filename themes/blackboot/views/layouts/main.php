<?php
Yii::app()->clientscript
// use it when you need it!
/*
  ->registerCssFile( Yii::app()->theme->baseUrl . '/css/bootstrap.css' )
  ->registerCssFile( Yii::app()->theme->baseUrl . '/css/bootstrap-responsive.css' )
  ->registerCoreScript( 'jquery' )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-transition.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-alert.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-modal.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-dropdown.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-scrollspy.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-tab.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-tooltip.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-popover.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-button.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-collapse.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-carousel.js', CClientScript::POS_END )
  ->registerScriptFile( Yii::app()->theme->baseUrl . '/js/bootstrap-typeahead.js', CClientScript::POS_END )
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/bootstrap-responsive.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/style.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/jqueryui/jquery-ui-1.9.2.custom.min.css" />

		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/custom.css" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<meta name="language" content="en" />
		<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<!-- Le styles -->


		<!-- Le fav and touch icons -->
	</head>

	<body>
		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<span class="brand"><?php echo Yii::app()->name ?></span>
					<div class="nav-collapse">
						<?php
						$this->widget('zii.widgets.CMenu', array(
							'htmlOptions' => array('class' => 'nav'),
							'activeCssClass' => 'active',
							'items' => array(
								array('label' => 'Home', 'url' => array('/site/index')),
								array('label' => Yii::t("labels", "Stations"), 'url' => array('/station/index')),
								array('label' => Yii::t("labels", "Graphs"), 'url' => array('/graphs/index')),
								array('label' => Yii::t("labels", "Positions"), 'url' => array('/position/index')),
								array('label' => Yii::t("labels", "Users"), 'url' => array('/user/index')),
								array('label' => 'Login', 'url' => array('/site/login'), 'visible' => Yii::app()->user->isGuest),
								array('label' => 'Logout (' . Yii::app()->user->name . ')', 'url' => array('/site/logout'), 'visible' => !Yii::app()->user->isGuest)
							),
						));
						?>

					</div><!--/.nav-collapse -->
				</div>
			</div>
		</div>

		<div class="cont">
			<div class="container-fluid">
				<div class="breadcrumb">
					<?php if (isset($this->breadcrumbs)): ?>
						<?php
						$this->widget('zii.widgets.CBreadcrumbs', array(
							'links' => $this->breadcrumbs,
							'homeLink' => false,
							'tagName' => 'ul',
							'separator' => '',
							'activeLinkTemplate' => '<li><a href="{url}">{label}</a> <span class="divider">/</span></li>',
							'inactiveLinkTemplate' => '<li><span>{label}</span></li>'
						));
						?>
						<!-- breadcrumbs -->
					<?php endif ?>
					<?php if ($this->id === 'tables' || $this->id === 'maps' || $this->id === 'graphs') : ?>
						<div class="breadLinks">
							<ul>

								<?php if ($this->id === 'graphs') : ?>
								<div class="clearfix"></div>
									<li>
										<div id="SelectDate">Select date</div>
										<div id="dateSelector"  class="jqueryui" ></div>
									</li>

									<li class="has">
										<?php
										$options = array(
											"last" => "Show latest data",
											"daily" => "Show daily data",
											"weekly" => "Show weekly data",
											"monthly" => "Show monthly data",
											"yearly" => "Show yearly data"
										);
										echo CHtml::dropDownList("intervalSelector", Yii::app()->user->getState('selected-interval'), $options);
										?>
									</li>
								<?php endif; ?>
								<li class="has">
									<?php
									$stations = Yii::app()->user->getStations();
									$options = array();
									foreach ($stations as $station) {
										$options[$station] = $station;
									}
									echo CHtml::dropDownList("stationSelector", Yii::app()->user->getState('selected-station'), $options);
									?>
								</li>
							</ul>
							<div class="clear"></div>
						</div>
					<?php endif; ?>
				</div>

				<?php foreach (Yii::app()->user->getFlashes() as $key => $message) : ?>
					<div class="nNote <?php echo $key; ?>"><p><?php echo $message; ?></p></div>
						<?php endforeach; ?>


				<?php echo $content ?>



			</div><!--/.fluid-container-->
		</div>
		<?php /*
		  <div class="extra">
		  <div class="container">
		  <div class="row">
		  <div class="col-md-3">
		  <h4>Heading 1</h4>
		  <ul>
		  <li><a href="#">Subheading 1.1</a></li>
		  <li><a href="#">Subheading 1.2</a></li>
		  <li><a href="#">Subheading 1.3</a></li>
		  <li><a href="#">Subheading 1.4</a></li>
		  </ul>
		  </div> <!-- /span3 -->

		  <div class="col-md-3">
		  <h4>Heading 2</h4>
		  <ul>
		  <li><a href="#">Subheading 2.1</a></li>
		  <li><a href="#">Subheading 2.2</a></li>
		  <li><a href="#">Subheading 2.3</a></li>
		  <li><a href="#">Subheading 2.4</a></li>
		  </ul>
		  </div> <!-- /span3 -->

		  <div class="col-md-3">
		  <h4>Heading 3</h4>
		  <ul>
		  <li><a href="#">Subheading 3.1</a></li>
		  <li><a href="#">Subheading 3.2</a></li>
		  <li><a href="#">Subheading 3.3</a></li>
		  <li><a href="#">Subheading 3.4</a></li>
		  </ul>
		  </div> <!-- /span3 -->

		  <div class="col-md-3">
		  <h4>Heading 4</h4>
		  <ul>
		  <li><a href="#">Subheading 4.1</a></li>
		  <li><a href="#">Subheading 4.2</a></li>
		  <li><a href="#">Subheading 4.3</a></li>
		  <li><a href="#">Subheading 4.4</a></li>
		  </ul>
		  </div> <!-- /span3 -->
		  </div> <!-- /row -->
		  </div> <!-- /container -->
		  </div>

		  <div class="footer">
		  <div class="container">
		  <div class="row">
		  <div id="footer-copyright" class="col-md-6">
		  About us | Contact us | Terms & Conditions
		  </div> <!-- /span6 -->
		  <div id="footer-terms" class="col-md-6">
		  © 2012-13 Black Bootstrap. <a href="http://nachi.me.pn" target="_blank">Nachi</a>.
		  </div> <!-- /.span6 -->
		  </div> <!-- /row -->
		  </div> <!-- /container -->
		  </div>
		 * */ ?>
	</body>
</html>
