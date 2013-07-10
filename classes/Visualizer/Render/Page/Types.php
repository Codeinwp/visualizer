<?php

class Visualizer_Render_Page_Types extends Visualizer_Render_Page {

	protected function _renderBody() {
		echo '<div id="type-picker">';
			foreach ( $this->types as $type ) {
				echo '<div class="type-box type-box-', $type, '">';
					echo '<label class="type-label', $type == $this->type ? ' type-label-selected' : '', '">';
						echo '<input type="radio" class="type-radio" name="type" value="', $type, '"', checked( $type, $this->type, false ), '>';
					echo '</label>';
				echo '</div>';
			}
		echo '</div>';
	}

	protected function _renderToolbar() {
		echo '<input type="submit" class="button button-primary button-large" value="', esc_attr__( 'Select Chart Type', Visualizer_Plugin::NAME ), '">';
	}

}