/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

import './controllers/form_signalement';
import './controllers/form_signalement_front';
import './controllers/form_signalement_erp_transport';
import './controllers/form_send_message';
import './controllers/form_send_estimation';
import './controllers/form_refuse_signalement';
import './controllers/form_admin_stop_signalement';
import './controllers/form_back_stop_intervention';
import './controllers/component_search_address';
import './controllers/component_search_commune';
import './controllers/component_file_auto_submit';
import './controllers/reinit_password';

import './controllers/list_signalement';
import './controllers/list_entreprises';
import './controllers/list_employes';
import './controllers/list_entreprises_publiques';

import './controllers/weekly_slider';
import './controllers/form_helper';
