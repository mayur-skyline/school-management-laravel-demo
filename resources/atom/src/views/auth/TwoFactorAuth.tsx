import { TOAST_OPTIONS } from '@astnext/common/configs';
import GreenButton from '@astnext/components/base/button/GreenButton';
import Icon from '@astnext/components/base/icon/Icon';
import Image from '@astnext/components/Image';
import WarningToast from '@astnext/components/toasts/WarningToast/WarningToast';
import { type StaffData, type StaffLoginResponse } from '@atom/common/models/Auth';
import TwoFactorAuthModel from '@atom/common/models/TwoFactorAuth';
import AuthLayout from '@atom/layouts/auth-layout/AuthLayout';
import { useNavigateWithCallbackURL } from '@atom/views/auth/hooks/useNavigateWithCallbackURL';
import KeyboardArrowLeftIcon from '@mui/icons-material/KeyboardArrowLeft';
import { Box, CircularProgress } from '@mui/material';
import IconButton from '@mui/material/IconButton';
import React, { useState } from 'react';
import { useCookies } from 'react-cookie';
import { toast } from 'react-toastify';

export type TwoFactorAuth = {
  loginData: Partial<StaffData>;
  responseData: StaffLoginResponse;
};

const CODE_SIZE = 6;

const TwoFactorAuth: React.FC<TwoFactorAuth> = ({ loginData, responseData }) => {
  const [code, setCode] = useState('');
  const [error, setError] = useState('');
  const [sendingCode, setSendingCode] = useState(false);
  const [codeSent, setCodeSent] = useState(false);

  const [verified, setVerified] = useState(false);
  const [verifying, setVerifying] = useState(false);
  const [signingIn, setSigningIn] = useState(false);

  const [encryptedCode, setEncryptedCode] = useState('');

  const [, setCookie] = useCookies(['jwt_token']);
  const { navigate } = useNavigateWithCallbackURL();

  function handleChange(value: string) {
    const substr = value.substring(0, CODE_SIZE);

    if (substr === '' || new RegExp(/^\d+$/).test(substr)) {
      setCode(substr);
      setError('');
      setVerified(false);
    } else {
      highlightInputWithWarning();
    }
  }

  function highlightInputWithWarning() {
    setError(' ');

    setTimeout(() => {
      setError('');
    }, 2000);
  }

  function handlePaste(val: string) {
    if (val && val.length) {
      handleChange(val);
    } else {
      highlightInputWithWarning();
      toast(<WarningToast>You have not copied anything</WarningToast>, TOAST_OPTIONS);
    }
  }

  async function handleButtonPaste() {
    try {
      if ((navigator as any).clipboard) {
        const pastedText = await (navigator as any).clipboard.readText();
        handlePaste(pastedText);
      } else {
        throw new Error('Clipboard API is not supported in this browser.');
      }
    } catch (error) {
      highlightInputWithWarning();
      toast(<WarningToast>{error.message}</WarningToast>, TOAST_OPTIONS);
    }
  }

  function resendCode(e) {
    e.preventDefault();

    setSendingCode(true);
    document.body.style.cursor = 'progress';

    TwoFactorAuthModel.resendToken(loginData.username)
      .catch((error) => {
        toast(
          <WarningToast>{error.message || 'Unable to resend the code.'}</WarningToast>,
          TOAST_OPTIONS
        );
      })
      .finally(() => {
        document.body.style.cursor = 'auto';
        setCodeSent(true);
        setSendingCode(false);

        setTimeout(() => {
          setCodeSent(false);
        }, 30000);
      });
  }

  function verifyCode(e) {
    e.preventDefault();

    setVerifying(true);

    TwoFactorAuthModel.verifyToken({ username: loginData.username, code })
      .then((data) => {
        setError('');
        setEncryptedCode(data.encrypted_code);
        setVerified(true);
      })
      .catch((error) => {
        setError(error.message || 'That wasn’t right');
      })
      .finally(() => {
        setVerifying(false);
      });
  }

  function handleSignIn(e) {
    e.preventDefault();
    setSigningIn(true);

    TwoFactorAuthModel.loginWithToken({
      code,
      username: loginData.username,
      encrypted_code: encryptedCode,
      remember_me: loginData.remember_me,
    })
      .then((data) => {
        if (typeof data === 'string') {
          setError(data);
        }
        if (data.staff_login_error) {
          setError(data.staff_login_error);
        } else {
          setCookie('jwt_token', data.session_data.token, { path: '/' });
          navigate(data.generate_session_url, `remember_me=${loginData.remember_me}`);
        }
      })
      .catch((error) => {
        setError(error.message || 'Something went wrong while signing in.');
      })
      .finally(() => {
        setSigningIn(false);
      });
  }

  return (
    <AuthLayout>
      <Box className="form-box">
        <div className="mb-[27px]">
          <span
            className="flex items-center gap-[4px] text-base text-gray-300 hover:text-black transition-colors duration-300 mt-2 -ml-2 cursor-pointer"
            onClick={() => {
              navigate('/new-login-view');
            }}
          >
            <KeyboardArrowLeftIcon />
            <span>Back</span>
          </span>
          <p className="text-[22px] mt-[7px] font-bold">We’ve sent you a code</p>
        </div>

        <div className="w-full flex items-center justify-between mb-[15px]">
          <p className="text-[16px] font-bold">{loginData.username}</p>

          {!verified && (
            <>
              {!codeSent ? (
                <button
                  className={`
                group text-[#514E6A] w-[95px] flex-center p-1 gap-[10px]
                ${sendingCode ? 'cursor-progress' : ''}
              `}
                  disabled={sendingCode}
                  onClick={resendCode}
                >
                  <Icon icon="send" size="14" />
                  {sendingCode ? (
                    <span>Sending</span>
                  ) : (
                    <span className="group-hover:underline">Resend it</span>
                  )}
                </button>
              ) : (
                <button
                  disabled={codeSent}
                  className="text-[#514E6A] w-[95px] flex-center p-1 gap-[10px] disabled:cursor-not-allowed"
                >
                  <Icon icon="send-green" size="14" />
                  <span>Sent</span>
                </button>
              )}
            </>
          )}
        </div>

        <Box
          component="form"
          noValidate
          autoComplete="off"
          className="flex-auto flex flex-col items-stretch"
          onSubmit={handleSignIn}
        >
          <div className="">
            <div
              className={`w-full h-[60px] flex items-center justify-between rounded-[10px] overflow-hidden border
              transition-all duration-300
              ${error ? 'border-[#C16363]' : 'border-[#E3E2E6]'}`}
            >
              <div className="bg-[#FAFAFD] flex-auto h-full flex items-center justify-between relative px-[20px]">
                <input
                  type="text"
                  placeholder="______"
                  value={code}
                  className={`
                    bg-transparent font-bold text-[22px] w-[110px] tracking-[4px] focus:outline-none
                    placeholder:text-[#DBDBDE] placeholder:font-normal
                    ${code.length < CODE_SIZE ? '' : 'caret-gray-200'}
                  `}
                  onPaste={(e) => handlePaste(e.clipboardData.getData('text'))}
                  onChange={(e) => handleChange(e.target.value || '')}
                />

                <div className="relative -right-[10px]">
                  <IconButton
                    className="opacity-40 hover:opacity-100 transition-all duration-300"
                    onClick={handleButtonPaste}
                  >
                    <Icon icon="paste" size="24" />
                  </IconButton>
                </div>
              </div>
              {!verified ? (
                <button
                  className={`
                    relative flex-shrink-0 h-full w-[120px] text-[#514E6A] font-bold px-[20px] transition-all duration-300 border-l
                    ${
                      verifying
                        ? 'cursor-progress bg-[#DBE9DB]'
                        : 'enabled:hover:bg-[#DBE9DB] disabled:cursor-not-allowed disabled:!opacity-50'
                    }
                    ${error ? 'border-[#C16363]' : 'border-[#E3E2E6]'}
                  `}
                  disabled={code.length < CODE_SIZE || verifying}
                  onClick={verifyCode}
                >
                  {verifying && (
                    <div className="absolute inset-0 z-10 flex-center text-green">
                      <CircularProgress size={16} color="inherit" />
                    </div>
                  )}
                  <div className={`flex-center gap-[4px] ${verifying ? 'invisible' : ''}`}>
                    <Icon icon="shield-green" size="24" className="mt-[2px]" />
                    <span>Verify</span>
                  </div>
                </button>
              ) : (
                <button className="flex-shrink-0 h-full w-[120px] text-white font-bold px-[20px] gap-[3px] bg-green border-l">
                  <div className="flex-center">
                    <Icon icon="tick-circle-white" size="32" className="mt-[4px]" />
                    <span>Verified</span>
                  </div>
                </button>
              )}
            </div>

            <div
              className={`
                flex items-center p-[6px] text-[12px] text-[#C16363] gap-[4px] h-[30px] transition-opacity
                ${error.trim() ? 'opacity-100' : 'opacity-0'}
              `}
            >
              <Icon icon="risk-alert" size="12" />
              <p>{error}</p>
            </div>
          </div>

          <div className="mb-[20px]">
            <div className="">
              <p className="font-bold">Check your spam if you can’t see the email!</p>

              <p className="opacity-40 text-[12px]">
                If you still can’t find it, reach out to your schools IT department as it may be
                related to your email settings.
              </p>
            </div>
          </div>

          <div className="h-[42px] mb-[40px]">
            {responseData?.school_2fa_status && (
              <div className="flex items-center gap-[20px]">
                <Image src="public/astnext/auth-pages/2fa-school.svg" className="flex-shrink-0" />
                <p className="text-[#514E6A]">
                  Two factor authentication has been set as a school-wide policy
                </p>
              </div>
            )}
          </div>

          <div>
            <GreenButton
              type="submit"
              loading={signingIn}
              className="w-[180px] h-[34px] disabled:!opacity-40"
              disabled={!verified || Boolean(error.length) || signingIn}
            >
              <span className="text-[14px] font-bold">Sign in</span>
            </GreenButton>
          </div>
        </Box>
      </Box>
    </AuthLayout>
  );
};

export default TwoFactorAuth;
