<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    Workplace_Authenticate
 * @copyright  Copyright (c) 2020 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Authenticate.php Monday 23rd of March 2020 09:36AM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Widget
 */

class Workplace_Authenticate extends Workplace
{
	
    /**
     * Access level for player. Defaults to everyone
     *
     * @var boolean
     */
	protected static $_accessLevel = array( 0 );
	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'Authenticate Client'; 

    /**
     * Returns user info from auth token
     * 
     * @param array Auth Info
     * @return mixed
     * 
     */
	public static function getAuthUserInfo( array $authInfo )
    {
        $table = Workplace_Authenticate_Table::getInstance();

        $userIdentifier = array( 
            'user_id' => $authInfo['user_id'],
            'auth_token' => $authInfo['auth_token'],
        );
        if( ! $auth = $table->selectOne( null, $userIdentifier ) )
        {
            return false;
        }
        $userInfo = Application_User_Abstract::getUserInfo( array( 'user_id' => $authInfo['user_id'] ) );

        return $userInfo;
    }

    /**
     * Performs the whole widget running process
     * 
     */
	public function init()
    {    
		try
		{ 
            //  Code that runs the widget goes here...
            if( empty( $_POST['email'] ) || empty( $_POST['password'] ) )
            {
                //  error
                $errorInfo = array(
                    'badnews' => 'email & password cannot be empty'
                );
                $this->_objectData = $errorInfo;
                return false;
            }
            $authInfo = array( 
                'username' => $_POST['email'],
                'password' => $_POST['password'],
            );

        //    var_export( $authInfo );
           
            if( $userInfo = Ayoola_Access_Login::localLogin( $authInfo ) )
            {
            //    var_export( $userInfo );
                $authToken = md5( uniqid( json_encode( $authInfo ), true ) );

                //  save auth info in data
                $table = Workplace_Authenticate_Table::getInstance();

                $authInfoToSave = array( 
                    'user_id' => $userInfo['user_id'],
                    'auth_token' => $authToken,
                    'device_info' => $_POST['device_info'],
                );

                $table->insert( $authInfoToSave );

                $this->_objectData = $authInfoToSave + $userInfo    ;

            }
            else
            {
                //  error
                $errorInfo = array(
                    'badnews' => 'Invalid email or password'
                );
                $this->_objectData = $errorInfo;
            }

             // end of widget process
          
		}  
		catch( Exception $e )
        { 
            //  Alert! Clear the all other content and display whats below.
        //    $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) ); 
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
