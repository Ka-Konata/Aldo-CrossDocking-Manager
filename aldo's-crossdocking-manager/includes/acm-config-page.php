<?php
/**
 * Settings Page (generated and a bit edited)
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 * 
 * Edited by: Victor G. Ramos
 */

class AldosCrossDockingManager {
	private $aldo_s_crossdocking_manager_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'aldo_s_crossdocking_manager_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'aldo_s_crossdocking_manager_page_init' ) );
	}

	public function aldo_s_crossdocking_manager_add_plugin_page() {
		add_menu_page(
			"Aldo's CrossDocking Manager - Configuration", // page_title
			"Aldo CD Manager", // menu_title
			'manage_options', // capability
			'aldo-s-crossdocking-manager', // menu_slug
			array( $this, 'aldo_s_crossdocking_manager_create_admin_page' ), // function
			'dashicons-download', // icon_url
			59 // position
		);
	}

	public function aldo_s_crossdocking_manager_create_admin_page() {
		$this->aldo_s_crossdocking_manager_options = get_option( 'aldo_s_crossdocking_manager_option_name' );
        ?>
		
		<style>
		    h2#acm-tittle {
		        font-weight: 500;
		    }
		    
		    .danger {
		        color: red;
		    }
		    
		    div#acm-settings > p {
		        font-size: 18px;
		        text-align: justify;
		    }
		    
		    div#acm-settings > form {
		        margin: 10px 25px;
		        padding: 0 15px;
		        background-color: #fff;
		        border: 1px solid gray;
		    }
		    
		    div#acm-settings input::-webkit-outer-spin-button, div#acm-settings input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            
            div#acm-settings input[type=number] {
                -moz-appearance:textfield;
            }
		    
		    @media (max-width: 500px) {
		        div#acm-settings > form {
		            margin: 10px 0;
		        }
		    }
		</style>

		<div class="wrap">
			<h2 id="acm-tittle">Aldo's CrossDocking Manager - Configuration</h2>
			<div id="acm-settings">
    			<p>Configure o plugin para que tudo funcione corretamente. É essencial que o código de autenticação e a chave 
    			sejam fornecidos corretamente para acessar a API da Aldo.</p>
    			<p><span class="danger"><strong>⚠️ IMPORTANTE:</strong> Caso utilize este plugin, todos os seus produtos 
    			adicionados anteriormente serão <strong>APAGADOS!!!</strong></span></br>Tenha certeza do que está fazendo antes usar o plugin!!</p>
    			<?php settings_errors(); ?>
    
    			<form method="post" action="options.php">
    				<?php
    					settings_fields( 'aldo_s_crossdocking_manager_option_group' );
    					do_settings_sections( 'aldo-s-crossdocking-manager-admin' );
    					submit_button();
    				?>
    			</form>
    		</div>
		</div>
	<?php }

	public function aldo_s_crossdocking_manager_page_init() {
		register_setting(
			'aldo_s_crossdocking_manager_option_group', // option_group
			'aldo_s_crossdocking_manager_option_name', // option_name
			'' // sanitize_callback
		);

		add_settings_section(
			'aldo_s_crossdocking_manager_setting_section', // id
			'Configurações', // title
			array( $this, 'aldo_s_crossdocking_manager_section_info' ), // callback
			'aldo-s-crossdocking-manager-admin' // page
		);

		add_settings_field(
			'cdigo_de_autenticao_0', // id
			'Código de Autenticação', // title
			array( $this, 'cdigo_de_autenticao_0_callback' ), // callback
			'aldo-s-crossdocking-manager-admin', // page
			'aldo_s_crossdocking_manager_setting_section' // section
		);

		add_settings_field(
			'chave_1', // id
			'Chave ', // title
			array( $this, 'chave_1_callback' ), // callback
			'aldo-s-crossdocking-manager-admin', // page
			'aldo_s_crossdocking_manager_setting_section' // section
		);

		add_settings_field(
			'horrio_para_atualizao_2', // id
			'Horário para atualização ', // title
			array( $this, 'horrio_para_atualizao_2_callback' ), // callback
			'aldo-s-crossdocking-manager-admin', // page
			'aldo_s_crossdocking_manager_setting_section' // section
		);

		add_settings_field(
			'enviar_notificacao_3', // id
			'Enviar Notificações', // title
			array( $this, 'enviar_notificacao_3_callback' ), // callback
			'aldo-s-crossdocking-manager-admin', // page
			'aldo_s_crossdocking_manager_setting_section' // section
		);

		add_settings_field(
			'enviar_inicio_5', // id
			'Enviar Notificações', // title
			array( $this, 'enviar_inicio_5_callback' ), // callback
			'aldo-s-crossdocking-manager-admin', // page
			'aldo_s_crossdocking_manager_setting_section' // section
		);

		add_settings_field(
			'e_mail_4', // id
			'E-mail', // title
			array( $this, 'e_mail_4_callback' ), // callback
			'aldo-s-crossdocking-manager-admin', // page
			'aldo_s_crossdocking_manager_setting_section' // section
		);
	}

	public function aldo_s_crossdocking_manager_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['cdigo_de_autenticao_0'] ) ) {
			$sanitary_values['cdigo_de_autenticao_0'] = sanitize_text_field( $input['cdigo_de_autenticao_0'] );
		}

		if ( isset( $input['chave_1'] ) ) {
			$sanitary_values['chave_1'] = sanitize_text_field( $input['chave_1'] );
		}

		if ( isset( $input['horrio_para_atualizao_2'] ) ) {
			$sanitary_values['horrio_para_atualizao_2'] = $input['horrio_para_atualizao_2'];
		}
		
		if ( isset( $input['enviar_notificacao_3'] ) ) {
			$sanitary_values['enviar_notificacao_3'] = $input['enviar_notificacao_3'];
		}

		if ( isset( $input['enviar_inicio_5'] ) ) {
			$sanitary_values['enviar_inicio_5'] = sanitize_text_field( $input['enviar_inicio_5'] );
		}

		if ( isset( $input['e_mail_4'] ) ) {
			$sanitary_values['e_mail_4'] = sanitize_text_field( $input['e_mail_4'] );
		}

		return $sanitary_values;
	}

	public function aldo_s_crossdocking_manager_section_info() {
		
	}

	public function cdigo_de_autenticao_0_callback() {
		printf(
			'<input class="regular-text" type="number" name="aldo_s_crossdocking_manager_option_name[cdigo_de_autenticao_0]" id="cdigo_de_autenticao_0" value="%s">
			<p>código de autenticação fornecida pela Aldo no após o pedido cadastro da revenda enviado para o e-mail cadastro@aldo.com.br (vai ser usado para consumir o xml com todos os produtos).</p>',
			isset( $this->aldo_s_crossdocking_manager_options['cdigo_de_autenticao_0'] ) ? esc_attr( $this->aldo_s_crossdocking_manager_options['cdigo_de_autenticao_0']) : ''
		);
	}

	public function chave_1_callback() {
		printf(
			'<input class="regular-text" type="password" name="aldo_s_crossdocking_manager_option_name[chave_1]" id="chave_1" value="%s">
			<p>chave fornecida pela Aldo no após o pedido cadastro da revenda enviado para o e-mail cadastro@aldo.com.br (vai ser usado para consumir o xml com todos os produtos).</p>',
			isset( $this->aldo_s_crossdocking_manager_options['chave_1'] ) ? esc_attr( $this->aldo_s_crossdocking_manager_options['chave_1']) : ''
		);
	}

	public function horrio_para_atualizao_2_callback() {
		?> <select name="aldo_s_crossdocking_manager_option_name[horrio_para_atualizao_2]" id="horrio_para_atualizao_2">
			<?php $selected = (isset( $this->aldo_s_crossdocking_manager_options['horrio_para_atualizao_2'] ) && $this->aldo_s_crossdocking_manager_options['horrio_para_atualizao_2'] === '5') ? 'selected' : '' ; ?>
			<option value="1603180800" <?php echo $selected; ?>>05:00 (recomendado)</option>
			<?php $selected = (isset( $this->aldo_s_crossdocking_manager_options['horrio_para_atualizao_2'] ) && $this->aldo_s_crossdocking_manager_options['horrio_para_atualizao_2'] === '8') ? 'selected' : '' ; ?>
			<option value="1603191600" <?php echo $selected; ?>>08:00</option>
			<?php $selected = (isset( $this->aldo_s_crossdocking_manager_options['horrio_para_atualizao_2'] ) && $this->aldo_s_crossdocking_manager_options['horrio_para_atualizao_2'] === '13') ? 'selected' : '' ; ?>
			<option value="1603206000" <?php echo $selected; ?>>12:00</option>
		</select>
		<p>Horário em que serão atualizados os produtos da Aldo, adicionando os novos e reconfigurando os antigos caso necessário. 
	O consumo do xml só pode ser feito das 04h às 16h, fora desse período é impossível fazer esse processo (o horário definido não será perfeitamente seguido).</p>
		<?php
	}

	public function enviar_notificacao_3_callback() {
		printf(
			'<input type="checkbox" name="aldo_s_crossdocking_manager_option_name[enviar_notificacao_3]" id="enviar_notificacao_3" value="enviar_notificacao_3" %s> <label for="enviar_notificacao_3">Envia um e-mail para informar sempre que os produtos forem atualizados</label>',
			( isset( $this->aldo_s_crossdocking_manager_options['enviar_notificacao_3'] ) && $this->aldo_s_crossdocking_manager_options['enviar_notificacao_3'] === 'enviar_notificacao_3' ) ? 'checked' : ''
		);
	}

	public function enviar_inicio_5_callback() {
		printf(
			'<input type="checkbox" name="aldo_s_crossdocking_manager_option_name[enviar_inicio_5]" id="enviar_inicio_5" value="enviar_inicio_5" %s> <label for="enviar_inicio_5">Envia um e-mail para informar sempre que os produtos forem atualizados</label>',
			( isset( $this->aldo_s_crossdocking_manager_options['enviar_inicio_5'] ) && $this->aldo_s_crossdocking_manager_options['enviar_inicio_5'] === 'enviar_inicio_5' ) ? 'checked' : ''
		);
	}

	public function e_mail_4_callback() {
		printf(
			'<input class="regular-text" type="text" placeholder="exemplo@site.com" name="aldo_s_crossdocking_manager_option_name[e_mail_4]" id="e_mail_4" value="%s">
			<p>Envia um e-mail para informar sempre que os produtos forem atualizados.</p>',
			isset( $this->aldo_s_crossdocking_manager_options['e_mail_4'] ) ? esc_attr( $this->aldo_s_crossdocking_manager_options['e_mail_4']) : ''
		);
	}

}
if ( is_admin() )
	$aldo_s_crossdocking_manager = new AldosCrossDockingManager();

/* 
 * Retrieve this value with:
 * $aldo_s_crossdocking_manager_options = get_option( 'aldo_s_crossdocking_manager_option_name' ); // Array of All Options
 * $cdigo_de_autenticao_0 = $aldo_s_crossdocking_manager_options['cdigo_de_autenticao_0']; // Código de Autenticação
 * $chave_1 = $aldo_s_crossdocking_manager_options['chave_1']; // Chave 
 * $horrio_para_atualizao_2 = $aldo_s_crossdocking_manager_options['horrio_para_atualizao_2']; // Horário para atualização 
 */