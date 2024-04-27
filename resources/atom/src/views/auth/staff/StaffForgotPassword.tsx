import Separator from '@astnext/components/base/separator/Separator';
import Image from '@astnext/components/Image';
import AuthLayout from '@atom/layouts/auth-layout/AuthLayout';
import ConfirmButton from '@atom/views/auth/components/ConfirmButton';
import { useNavigateWithCallbackURL } from '@atom/views/auth/hooks/useNavigateWithCallbackURL';
import CancelIcon from '@mui/icons-material/Cancel';
import KeyboardArrowLeftIcon from '@mui/icons-material/KeyboardArrowLeft';
import { Box } from '@mui/material';
import React, { type FC, useState } from 'react';

import ForgotPasswordModel, { type IForgotData } from '../../../common/models/ForgotPassword';
const initialValue = {
  username: '',
};

const school_id = sessionStorage.getItem('school_id') ?? '319';

const StaffForgotPassword: FC = () => {
  const [fdata, setFdata] = useState<IForgotData>();
  const [emailData, setEmailData] = useState(initialValue);
  const [showElement, setShowElement] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const { navigate } = useNavigateWithCallbackURL();

  const ForgotDatas = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitting(true);
    ForgotPasswordModel.getForgotData({
      school_id: school_id,
      ...emailData,
    })
      .then((data) => {
        setFdata(data);
        setShowElement(true);
        if (data.success) {
          sessionStorage.setItem('confirmMsg', 'success msg');
          window.location.reload();
        }
      })
      .finally(() => {
        setSubmitting(false);
      });
  };
  const EmailMsgGet = sessionStorage.getItem('confirmMsg');

  const handleReEnterEmail = () => {
    sessionStorage.removeItem('confirmMsg');
    window.location.reload();
  };

  return (
    <AuthLayout>
      {!EmailMsgGet ? (
        <Box className="form-box">
          <div className="-mt-4">
            <span
              className="text-base text-gray-300 mt-2 -ml-2 cursor-pointer"
              onClick={() => {
                navigate('/new-login-view');
              }}
            >
              <KeyboardArrowLeftIcon />
              Back
            </span>
            <p className="text-2xl mt-2 font-bold">Forgotten Password</p>
            <p className="text-lg text-gray-300 mt-2">Teacher</p>
          </div>
          <Box
            component="form"
            sx={{
              '& > :not(style)': { width: '23rem', p: '10px 0px 5px' },
            }}
            noValidate
            autoComplete="off"
            className="flex-auto flex flex-col items-stretch"
            onSubmit={ForgotDatas}
          >
            <p className="mt-8 text-base ">
              {`Enter Your Email and we'll send a link to reset it`}
            </p>
            <input
              type="text"
              placeholder="Enter email"
              className="focus:outline-none mt-8"
              onChange={(e) => setEmailData({ ...emailData, username: e.target.value })}
            />
            <Image src="/public/astnext/auth-pages/horizontal-line-url.png" />
            <div className="h-12">
              {fdata?.error && showElement && (
                <div className="h-7 mt-4">
                  <span className="text-gray-500 text-sm">
                    <CancelIcon fontSize="small" />
                    <span className="ml-2">{fdata.error}</span>
                  </span>
                </div>
              )}
            </div>

            <Separator />

            <div>
              <ConfirmButton type="submit" loading={submitting} className="w-[155px]" />
            </div>
          </Box>
        </Box>
      ) : (
        <Box className="form-box">
          <span
            className="text-base text-gray-300 cursor-pointer relative -mt-4"
            onClick={handleReEnterEmail}
          >
            <KeyboardArrowLeftIcon />
            Back
          </span>
          <div className="w-full mt-8 flex-center flex-col">
            <div className="flex-center">
              <div
                className="rounded-xl flex items-center justify-center h-40 w-40"
                style={{
                  backgroundColor: '#e3efe5',
                  left: '140px',
                  top: '72px',
                }}
              >
                <div
                  className="items-start rounded-md box-border flex flex-row h-12 justify-end left-12 top-14 w-16"
                  style={{ backgroundColor: '#519659' }}
                >
                  <Image
                    className="bg-transparent shrink h-7 -mt-px object-cover relative w-16"
                    src="/public/astnext/auth-pages/email-img-url.png"
                  />
                </div>
              </div>
            </div>
            <p
              className="w-3/4 mt-12 text-center font-500 leading-6 text-text-base"
              style={{
                color: '#545355',
              }}
            >
              Thanks, If that email is connected to an account, you will receive a link to reset
              your password
            </p>
            <p className="font-extrabold mt-4 text-center">Didn’t get it?</p>
            <p
              className="mt-4 text-center font-bold cursor-pointer"
              style={{ color: '#519659' }}
              onClick={handleReEnterEmail}
            >
              Re-enter email
            </p>

            <p className="text-sm text-gray-400 mt-4 text-center">
              Make sure to check your spam folder
            </p>
          </div>
        </Box>
      )}
    </AuthLayout>
  );
};

export default StaffForgotPassword;
