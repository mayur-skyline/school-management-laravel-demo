import './AuthLayout.scss';

import Image from '@astnext/components/Image';
import ToastContainerWrapper from '@astnext/components/toasts/ToastContainer/ToastContainer';
import { useNavigateWithCallbackURL } from '@atom/views/auth/hooks/useNavigateWithCallbackURL';
import React, { useState } from 'react';
import urlJoin from 'url-join';

type AuthLayout = React.HTMLAttributes<HTMLElement>;

const AuthLayout: React.FC<AuthLayout> = ({ children }) => {
  const [navigating, setNavigating] = useState(false);
  const { navigate } = useNavigateWithCallbackURL();

  const LoginBg = urlJoin(
    process.env.MIX_AST_ASSETS_URL || '',
    '/public/astnext/auth-pages/login-background.png'
  );

  function handleSelectGroupDashboard(e) {
    e.preventDefault();
    setNavigating(true);
    sessionStorage.setItem('school_id', '37');
    sessionStorage.setItem('school_code', '999037');
    sessionStorage.setItem('school_name', 'STEER Group Dashboard');
    sessionStorage.setItem('is_school_auth', 'true');
    sessionStorage.setItem('select', 'Teacher');

    setTimeout(() => {
      navigate('/login-view-form');
      setNavigating(false);
    }, 100);
  }

  return (
    <div>
      <ToastContainerWrapper />
      <div
        className="fixed inset-0 w-full h-full"
        style={{
          backgroundImage: `url(${LoginBg})`,
          backgroundPosition: 'center',
          backgroundRepeat: 'no-repeat',
          backgroundSize: 'cover',
        }}
      />
      <div className="w-full flex flex-col gap-[36px] py-[45px] px-16 lg:px-[75px] min-h-screen">
        <Image
          src="/public/astnext/images/steer-logo-new.png"
          className="flex-shrink-0 relative w-[167px] h-[50px]"
        />

        <div className="min-h-[calc(100vh-176px)] flex-center">
          <div className="w-full relative flex flex-wrap gap-[56px] lg:flex-nowrap">
            <div className="w-full lg:w-3/5 flex items-start justify-center gap-[13px] my-10">
              {/*<Image*/}
              {/*  src="/public/astnext/auth-pages/login-splash.png"*/}
              {/*  className="flex-shrink-0 w-[266px] h-[266px]"*/}
              {/*/>*/}
              <div>
                <h1 className="text-[30px] font-bold mb-[21px] max-w-[376px]">
                  Interested in STEER Tracking for your school?
                </h1>

                <a href="https://steer.education/" target="_blank" rel="noreferrer noopener">
                  <button className="h-[62px] w-[293px] bg-[#F07216] rounded-full flex-center gap-[27px] hover:opacity-80 transition-opacity duration-300">
                    <p className="text-[21px] font-bold ml-[25px] text-white">Go to our website</p>
                    <Image
                      src="/public/astnext/auth-pages/play-button.png"
                      className="flex-shrink-0 w-[40px] h-[40px]"
                    />
                  </button>
                </a>

                <button
                  disabled={navigating}
                  className="disabled:cursor-progress flex-center mt-[32px] gap-[23px] w-[342px] h-[102px] bg-[#FFFFFF3B] hover:bg-[#FFFFFF6E] p-[16px] rounded-[13px] transition-colors duration-300"
                  onClick={handleSelectGroupDashboard}
                >
                  <Image
                    src="public/astnext/auth-pages/group-dash.svg"
                    className="w-[87px] h-[67px]"
                  />

                  <div className="text-left">
                    <p className="font-bold mb-[5px]">Using STEER to track your school group?</p>
                    <p className="font-bold text-[#ED6400]">Log in to Group Dashboard</p>
                  </div>
                </button>
              </div>
            </div>

            <div className="w-full lg:w-3/5 flex-center">{children}</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AuthLayout;
