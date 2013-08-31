<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by JetBrains PhpStorm.
 * User: dhc
 * Date: 13-8-30
 * Time: 下午10:51
 * To change this template use File | Settings | File Templates.
 */

class User extends CI_Controller
{

    private $uid;
    private $fid;
    private $data;
    private $mobilefee;
    private $unicomfee;
    private $telecomfee;

    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('is_login') != 1 || $this->session->userdata('usertype') ==1)
            redirect();
        $this->load->model('user_model');
        $this->load->model('fee_model');
        $this->load->model('contact_model');
        $this->load->model('sms_model');

        //私有变量初始化
        $this->uid = $this->session->userdata('uid');
        $this->fid = $this->session->userdata('fid');
        $this->usertype = $this->session->userdata('usertype');
        $this->data['fname'] = $this->session->userdata('fname');
        $this->data['usrname'] = $this->session->userdata('usrname');
        $this->mobilefee = $this->fee_model->get_factory_fee($this->fid, 1);
        $this->unicomfee = $this->fee_model->get_factory_fee($this->fid, 2);
        $this->telecomfee = $this->fee_model->get_factory_fee($this->fid, 3);
        $this->data['fee'] = array($this->mobilefee, $this->unicomfee, $this->telecomfee);
    }

    public function index()
    {
        $this->load->view('user/header', $this->data);
        $this->load->view('user/welcome', $this->data);
    }

    public function generalsms($type = null)
    {
        $data = $this->input->post();
        if (!$data) {
            $data = $this->contact_model->selectContact(null, $this->uid);
            if ($data)
                $this->data['contact'] = $data;
            $data = $this->contact_model->selectContactgroup($this->uid);
            if ($data)
                $this->data['contactgroup'] = $data;
            $this->load->view('user/header', $this->data);
            $this->load->view('user/sms/header', $this->data);
            $this->load->view('user/sms/generalsms', $this->data);
        } else {
            if (mb_strlen($data['content']) > 70 || strlen($data['number']) > 11999) {
                $this->session->set_flashdata('err', '填写信息错误');
                redirect(base_url('user/generalsms'));
            } else {
                $data['number'] = array_map(array($this, 'judgeISP'), explode(",", $data['number']));
                if (in_array('err', $data['number'])) {
                    $this->session->set_flashdata('err', '号码非法');
                    redirect(base_url('user/generalsms'));
                } else {
                    $content = $data['content'];
                    $result = $this->sms_model->addMtcontent($this->uid, $content);
                    if ($this->submitSmstask($data['number'], $result)) {
                        $this->session->set_flashdata('err', '短信任务添加成功');
                        redirect(base_url('user/generalsms'));
                    }
                }
            }
        }
    }

    public function multisms()
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/sms/header', $this->data);
            $this->load->view('user/sms/multisms');
        } else {
            $title = trim($data['title']);
            $content = trim($data['content']);
            if (strlen($title) == 0 || strlen($content) == 0 || strlen($data['number']) == 0) {
                $this->session->set_flashdata('err', '请填写所有项');
            } else {
                $data['number'] = array_map(array($this, 'judgeISP'), explode(",", trim($data['number'])));
                if (in_array('err', $data['number'])) {
                    $this->session->set_flashdata('err', '号码非法');
                } else {
                    $result = $this->sms_model->addMtcontent($this->uid, $content, $title);
                    if ($this->submitSmstask($data['number'], $result)) {
                        $this->session->set_flashdata('err', '短信任务添加成功');
                    }
                }
            }
        }
    }

    public function smsbox($type = null)
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/smsbox', $this->data);
        }
        else{
            switch($type){
                case 'select':
                    $data = array_filter($data);
                    $result = $this->sms_model->getHistorysms($this->uid, $data);
                    if($result){
                        $result['result'] = $result;
                        $this->load->view('user/header', $this->data);
                        $this->load->view('user/smsbox', $result);
                    }else{
                        $this->session->set_flashdata('err', '没有找到结果');
                        redirect(base_url('user/smsbox'));
                    }
            }
        }
    }

    public function sendbox($type = null)
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/sendbox', $this->data);
        }
        else{
            switch($type){
                case 'select':
                    $data = array_filter($data);
                    $result = $this->sms_model->getHistorysms($this->uid, $data);
                    if($result){
                        $result['result'] = $result;
                        $this->load->view('user/header', $this->data);
                        $this->load->view('user/sendbox', $result);
                    }else{
                        $this->session->set_flashdata('err', '没有找到结果');
                        redirect(base_url('user/sendbox'));
                    }
                    break;
                default:
                    redirect(base_url('user/sendbox'));
            }
        }
    }

    public function addcontact()
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/contact/header', $this->data);
            $result['cgroup'] = $this->contact_model->selectContactgroup($this->uid);
            $this->load->view('user/contact/addcontact', $result);
        } else {
            $data = array_filter($data);
            if (count($data) < 5 || (count($data) == 5 && array_key_exists('cinfo', $data))) {
                $this->session->set_flashdata('err', '请填写所有必填项');
                redirect(base_url('user/addcontact'));
            }
            $data['uid'] = $this->uid;
            if ($this->contact_model->addContact($data))
                $this->session->set_flashdata('err', '操作成功');
            else {
                $this->session->set_flashdata('err', '操作失败，请检查你的输入');
            }
            redirect(base_url('user/addcontact'));
        }
    }

    public function maintaincontact($type = null)
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/contact/header', $this->data);
            $this->load->view('user/contact/maintaincontact', $this->data);
        } else {
            switch ($type) {
                case 'select':
                    $data = array_filter($data);
                    if (count($data) > 2)
                        redirect('user/maintaincontact');
                    else {
                        $result = $this->contact_model->selectContact($data, $this->uid);
                        if ($result) {
                            $result['contact'] = $result['result'];
                            $this->load->view('user/header', $this->data);
                            $this->load->view('user/contact/header', $this->data);
                            $this->load->view('user/contact/maintaincontact', $result);
                        } else {
                            $this->session->set_flashdata('err', '没有找到');
                            redirect(base_url('user/maintaincontact'));
                        }
                    }
                    break;
                default:
                    redirect(base_url('user/maintaincontact'));
            }
        }
    }

    public function importcontact()
    {
        $this->load->view('user/header', $this->data);
        $this->load->view('user/contact/header', $this->data);
        $this->load->view('user/contact/importcontact', $this->data);

    }

    public function addcontactgroup()
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/contact/header', $this->data);
            $this->load->view('user/contact/addcontactgroup', $this->data);
        } else {
            $insertdata['cgname'] = trim($data['cgname']);
            $insertdata['cginfo'] = trim($data['cginfo']);
            if (strlen($insertdata['cgname']) == 0 || strlen($insertdata['cginfo']) == 0) {
                $this->session->set_flashdata('err', '输入信息错误');
                redirect('user/addcontactgroup');
            } else {
                $insertdata['uid'] = $this->uid;
                if($this->contact_model->addContactgroup($insertdata)){
                    $this->session->set_flashdata('err', '联系人组添加成功');
                    redirect('user/addcontactgroup');
                }
                else{
                    $this->session->set_flashdata('err', '联系人组添加失败，请检查你的输入');
                    redirect('user/addcontactgroup');
                }
            }
        }
    }

    public function maintaincontactgroup($type = null)
    {
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/contact/header', $this->data);
            $this->load->view('user/contact/maintaincontactgroup', $this->data);
        } else {
            switch ($type) {
                case 'select':
                    $cgname = trim($data['cgname']);
                    $cginfo = trim($data['cginfo']);
                    $result = $this->contact_model->selectContactgroup($this->uid, $cgname, $cginfo);
                    if($result){
                        $result['contactgroup'] = $result;
                        $this->load->view('user/header', $this->data);
                        $this->load->view('user/contact/header', $this->data);
                        $this->load->view('user/contact/maintaincontactgroup', $result);
                    }else{
                        $this->session->set_flashdata('err', '没有找到');
                        redirect('user/maintaincontactgroup');
                    }
                    break;
                default:
                    redirect(base_url('user/maintaincontactgroup'));
            }
        }
    }

    public function addphrase()
    {
        $this->load->view('user/header', $this->data);
        $this->load->view('user/phrases/header', $this->data);
        $this->load->view('user/phrases/addphrase', $this->data);
    }

    public function managephrase()
    {
        $this->load->view('user/header', $this->data);
        $this->load->view('user/phrases/header', $this->data);
        $this->load->view('user/phrases/managephrase', $this->data);

    }

    public function addphrasecategory()
    {
        $this->load->view('user/header', $this->data);
        $this->load->view('user/phrases/header', $this->data);
        $this->load->view('user/phrases/addphrasecategory', $this->data);
    }

    public function managephrasecategory()
    {
        $this->load->view('user/header', $this->data);
        $this->load->view('user/phrases/header', $this->data);
        $this->load->view('user/phrases/managephrasecategory', $this->data);
    }

    public function manage($type = null)
    {
        if($this->session->userdata('usertype') == 0)
            redirect(base_url('admin/manage'));
        $data = $this->input->post();
        if (!$data) {
            $this->load->view('user/header', $this->data);
            $this->load->view('user/manage');
        } else {
            switch($type){
                case 'select':
                    $usrname = trim($data['usrname']);
                    if(strlen($usrname) == 0)
                        $result = $this->user_model->manage_factory_user($this->fid);
                    else
                        $result = $this->user_model->manage_factory_user($this->fid, $usrname);
                    if($result){
                        $result['user'] = $result;
                        $this->load->view('user/header', $this->data);
                        $this->load->view('user/manage', $result);
                    }else{
                        redirect(base_url('user/manage'));
                    }
                    break;
                default:
                    redirect(base_url('user/manage'));
            }
        }
    }

    public function uploadcallback()
    {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'txt';
        $config['max_size'] = '2000';
        $config['overwrite'] = TRUE;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            ;
        } else {
            $this->load->helper('file');
            $data = $this->upload->data();
            echo $string = read_file('./uploads/' . $data['file_name']);
        }
    }

    private function judgeISP($data)
    {
        if (strlen($data) == 11) {
            $prefix = substr($data, 0, 3);
            if (in_array($prefix, array(
                134, 135, 136, 137, 138, 139, 150, 151, 152, 157, 158, 159, 147, 182, 183, 184, 187, 188
            ))
            )
                $data = array('number' => $data, 'type' => 'mobile');
            else if (in_array($prefix, array(
                130, 131, 132, 145, 155, 156, 185, 186, 145
            ))
            )
                $data = array('number' => $data, 'type' => 'unicom');
            else if (in_array($prefix, array(
                133, 153, 180, 181, 189
            ))
            )
                $data = array('number' => $data, 'type' => 'telecom');
            else
                $data = 'err';
        } else {
            $data = 'err';
        }
        return $data;
    }

    private function submitSmstask($data, $csid)
    {
        $insertdata = array();
        $mobilefee = 0;
        $unicomfee = 0;
        $telecomfee = 0;
        foreach ($data as $item) {
            $item = array(
                'csid' => $csid,
                'uid' => $this->uid,
                'type' => ($item['type'] == 'mobile' ? 1 : ($item['type'] == 'unicom' ? 2 : 3)),
                'snumber' => $item['number'],
                'flag' => 0
            );
            switch ($item['type']) {
                case 1:
                    $mobilefee++;
                    break;
                case 2:
                    $unicomfee++;
                    break;
                default:
                    $telecomfee++;
            }
            if ($mobilefee > $this->mobilefee || $unicomfee > $this->unicomfee || $telecomfee > $this->telecomfee) {
                $this->session->set_flashdata('err', '短信余额不足');
                redirect(base_url('user/generalsms'));
            }
            array_push($insertdata, $item);
        }
        //计费
        $this->fee_model->update_factory_fee($this->fid, 1, $this->mobilefee - $mobilefee);
        $this->fee_model->update_factory_fee($this->fid, 2, $this->unicomfee - $unicomfee);
        $this->fee_model->update_factory_fee($this->fid, 3, $this->telecomfee - $telecomfee);

        $this->sms_model->addSmstask($insertdata);
        return TRUE;
    }
}