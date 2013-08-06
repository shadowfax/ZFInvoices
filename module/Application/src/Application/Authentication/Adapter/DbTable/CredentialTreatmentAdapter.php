<?php


namespace Application\Authentication\Adapter\DbTable;

use Zend\Crypt\Password\Bcrypt;

use Zend\Authentication\Adapter\DbTable\AbstractAdapter;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql;
use Zend\Db\Sql\Expression as SqlExpr;
use Zend\Db\Sql\Predicate\Operator as SqlOp;


class CredentialTreatmentAdapter extends AbstractAdapter
{

    /**
     * __construct() - Sets configuration options
     *
     * @param DbAdapter $zendDb
     * @param string    $tableName           Optional
     * @param string    $identityColumn      Optional
     * @param string    $credentialColumn    Optional
     */
    public function __construct(
        DbAdapter $zendDb,
        $tableName = null,
        $identityColumn = null,
        $credentialColumn = null
    ) {
        parent::__construct($zendDb, $tableName, $identityColumn, $credentialColumn);
    }


    /**
     * _authenticateCreateSelect() - This method creates a Zend\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
     */
    protected function authenticateCreateSelect()
    {
    	// get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->tableName)
            ->columns(array('*'))
            ->where(new SqlOp($this->identityColumn, '=', $this->identity));

        return $dbSelect;
    }

    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
    	/*
    	if ($resultIdentity['zend_auth_credential_match'] != '1') {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->authenticateCreateAuthResult();
        }
        */
    	// Compare DB Hash against User generated hash
    	$bcrypt = new Bcrypt();
        if (!$bcrypt->verify($this->credential, $resultIdentity[$this->credentialColumn])) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }
    	
        unset($resultIdentity['zend_auth_credential_match']);
        $this->resultRow = $resultIdentity;

        $this->authenticateResultInfo['code']       = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->authenticateCreateAuthResult();
    }
}