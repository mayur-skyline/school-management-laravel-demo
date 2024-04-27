import '@astnext/services/sentry.service';

import ForgotPassword from '@atom/views/auth/ForgotPassword';
import NewResetPassword from '@atom/views/auth/NewResetPassword';
import SelectSchool from '@atom/views/auth/SelectSchool';
import UserLogin from '@atom/views/auth/UserLogin';
import React from 'react';
import ReactDOM from 'react-dom';

const exportComponents = {
  'new-login-view': <SelectSchool />,
  'login-view-form': <UserLogin />,
  'new-forgot-password': <ForgotPassword />,
  'new-forgot-password-step2': <NewResetPassword />,
};

Object.keys(exportComponents).map((compName) => {
  if (document.getElementById(compName)) {
    ReactDOM.render(exportComponents[compName], document.getElementById(compName));
  }
});
