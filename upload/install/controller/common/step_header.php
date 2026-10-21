<?php
/** Shared installer step header, derives the step from the current route. */
class ControllerCommonStepHeader extends Controller {
    public function index() {
        $route = isset($this->request->get['route']) ? (string)$this->request->get['route'] : 'install/step_1';
        $step = 1;
        if (preg_match('~^install/step_([1-5])(?:/.*)?$~', $route, $matches)) {
            $step = (int)$matches[1];
        }

        $data = array();
        $data['step_number'] = $step;
        $data['step_total'] = 5;
        $data['is_final_step'] = ($step === 5);
        $data['heading_title'] = $this->language->get('heading_title');
        // Final screen currently uses the text_step_4 language key.
        $data['step_description'] = $this->language->get('text_step_' . ($step === 5 ? 4 : $step));

        return $this->load->view('common/step_header', $data);
    }
}
