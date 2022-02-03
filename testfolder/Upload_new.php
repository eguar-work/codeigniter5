<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload_new extends CI_Controller
{
    function index()
    {

        $resp = array();
        $this->load->model('Model_seaman');
        $this->load->model('Model_log_files');
        $this->load->model('Model_admin_config');

        $doc_id = $this->input->post('doc_id');


        $seaman_id= $this->session->userdata('seaman_id');
        $server = $_SERVER["DOCUMENT_ROOT"]."/";
        $folder = MAIN_UNIX."/img/seaman_files/";
        $seaman_folder=$server.$folder.'seaman_'.$seaman_id;
        $path = "/var/www/html/".$folder."seaman_".$seaman_id;
        $path2 = $folder."seaman_".$seaman_id;

        $error = "";
        $msg = "";

        $fileElementName = 'file';
        if(!empty($_FILES[$fileElementName]['error']))
        {
            switch($_FILES[$fileElementName]['error'])
            {
                case '1':
                    $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                    break;
                case '2':
                    $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                    break;
                case '3':
                    $error = 'The uploaded file was only partially uploaded';
                    break;
                case '4':
                    $error = 'No file was uploaded.';
                    break;
                case '6':
                    $error = 'Missing a temporary folder';
                    break;
                case '7':
                    $error = 'Failed to write file to disk';
                    break;
                case '8':
                    $error = 'File upload stopped by extension';
                    break;
                case '999':
                default:
                    $error = 'No error code avaiable';
            }
            $resp['error'] = " Check 1".$error;

            $data = array(
                "msg" => "UPLOAD Cancell",
                "adr" => "",
                "path" => "",
                "size" => "",
                "Error" => $resp['error']
            );
            echo json_encode($data);


        }
        elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none')
        {
            print_r($_FILES);
            die();
            $error = 'No file was uploaded..';
            $resp['error'] = $error;
            $data = array(
                "msg" => "UPLOAD Cancell",
                "adr" => "",
                "path" => "",
                "size" => "",
                "Error" => "CHECK 2 : ".$resp['error']
            );
            echo json_encode($data);
        }
        else
        {

            $ext_array=explode('.', $_FILES[$fileElementName]['name']);
            $count=count($ext_array)-1;
            $ext=strtolower ($ext_array[$count]);

            $img = $seaman_folder.'/'.'photo_'.$seaman_id;
            $path.="/photo_".$seaman_id.".".$ext;
            $path2.="/photo_".$seaman_id.".".$ext;
            $extansion_array=array('jpg','png','gif', 'jpeg', 'pdf');
            if(!in_array($ext, $extansion_array))
            {
                $resp["error"] = "Wrong file_type";

            }
            $size = $_FILES[$fileElementName]['size'];
            $max_size_info = $this->Model_admin_config->get_values(10); // Max Upload
            $max_size = $max_size_info[0][FIELD_ADMIN_CONFIG_VAL];
            if (($size/1024/1024)>$max_size)
            {
                $resp["error"] = "Too Long File";

            }



            // @unlink($img.".".$extansion_array[$k]);



            $seaman_prev_photo = $this->Model_seaman->get_photo($seaman_id);


            if($doc_id) {
                $fname = "docs_" . $doc_id.".".$ext;
            }
            else
            {
                $fname = date('Y-m-d_H_i_s').".".$ext;
            }

            $new_photo = SEAMAN_PHOTO_PREF.$seaman_id.".".$ext;


            $date_now = date('YmdHis');
            $this->session->set_userdata('upl_time', $date_now);



            $file_without_ext_arr = explode('.', $fname);
            $file_without_ext = $file_without_ext_arr[0];
            $file_without_ext_plus_date = $file_without_ext."?".$date_now.".".$file_without_ext_arr[1];


            //$file_temp = "upl_test/".$file_without_ext_plus_date;
            $file_temp = "upl_test/".$fname;


            $data = array();
            if(!$resp['error'])
            {
                if (move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $file_temp))
                {

                    $new_adr = "seaman_" . $seaman_id . "/" . "photo_" . $seaman_id . "." . $ext;
                    $path = "/var/www/html/imaritime/".$file_temp;
                    if(is_file($path))
                    {
                        $filesize = filesize($path);
                    }
                    else
                    {
                        $filesize = array();
                    }


                    //$filesize = $filesize / 1000000; // Mb
                    $size = getimagesize($path);
                    $width = $size[0];
                    $height = $size[1];
                    $count_seaman_img = $this->Model_log_files->get_seaman_log($seaman_id);


                    $new_link = BUCKET_SEAMANS . SEAMANS_PREF . $seaman_id . "/" . SEAMAN_PHOTO_PREF . $seaman_id . "." . $ext;

                    //$s4 = new Aws_api();
                    // $new_ll = $s4->get_link($new_link);

                    // array("name" => $filename,"size" => $filesize, "src"=> $src);

                    $this->session->set_userdata('last_doc_upl', $file_temp);

                    $msg .= "UPLOAD SUCCESS";


                    $link = $file_temp;


                    $file_temp_exp = explode('.', $file_temp);
                    if(end($file_temp_exp)=="Pdf" || end($file_temp_exp)=="pdf")
                    {
                        $link = "img/adobe.png";
                    }


                    $data = array(
                        "msg" => "UPLOAD SUCCESS",
                        "adr" => "/imaritime/".$file_temp,
                        "src" => "/imaritime/".$link,
                        "session" => $file_temp,
                        "size" => $filesize,
                        "Error" => $error
                    );
                    echo json_encode($data);
                }
            }


            else
            {
                $data = array(
                    "msg" => "UPLOAD Cancell",
                    "adr" => "",
                    "path" => "",
                    "size" => "",
                    "Error" => "CHECK 3 : ".$resp['error']
                );
                echo json_encode($data);
            }

        }





    }
}
