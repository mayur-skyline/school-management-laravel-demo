import { type SessionType } from '@astnext/common/models/Session';
import { useSessionStorage } from '@astnext/hooks/useSessionStorage';
import ExecutiveSummary from '@astnext/views/admin-dashboard/views/executive-summary/ExecutiveSummary';
import ImportStaffsPage from '@astnext/views/admin-dashboard/views/new-year-setup/views/ImportStaffsPage';
import ImportStudentsPage from '@astnext/views/admin-dashboard/views/new-year-setup/views/ImportStudentsPage';
import LoginCardsPrintView from '@astnext/views/app-dashboard/assessment-tracker/views/LoginCardsPrintView';
import HalfLogin from '@astnext/views/app-dashboard/half-login/HalfLogin';
import Reports from '@astnext/views/app-dashboard/reports/Reports';
import SafeguardingV2 from '@astnext/views/app-dashboard/safeguarding-v2/SafeguardingV2';
import ActionPlanRoutes from '@astnext/views/app-dashboard/school-actions/views/action-plan-routes/ActionPlanRoutes';
import CascadeTrainingModel from '@astnext/views/app-dashboard/school-actions/views/action-plan-routes/views/CascadeTrainingModel';
import PeerMonitoringTrainingModel from '@astnext/views/app-dashboard/school-actions/views/action-plan-routes/views/PeerMonitoringTrainingModel';
import WholeSchoolTrainingModel from '@astnext/views/app-dashboard/school-actions/views/action-plan-routes/views/WholeSchoolTrainingModel';
import TrackingConsent from '@astnext/views/app-dashboard/tracking-consent/TrackingConsent';
import TrainingV2 from '@astnext/views/app-dashboard/training/TrainingV2';
import SelfRegulationExplained from '@astnext/views/app-dashboard/training/views/self-regulation-explained/SelfRegulationExplained';
import React, { useMemo } from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router-dom';

import { parseQuery } from '../common/helpers';
import { useStore } from '../hooks/useStore';
import AppLayout from '../layouts/app-layout/AppLayout';
import AdminDashboard from '../views/admin-dashboard/AdminDashboard';
import NewExecutive from '../views/admin-dashboard/views/executive-summary-report/NewExecutive';
import AssessmentTracker from '../views/app-dashboard/assessment-tracker/AssessmentTracker';
import ExportData from '../views/app-dashboard/export-data/ExportData';
import NewYearSetup from '../views/app-dashboard/new-year-setup/NewYearSetup';
import QuickVideo from '../views/app-dashboard/PSHE/components/quick-video/QuickVideo';
import PSHE from '../views/app-dashboard/PSHE/PSHE';
import FootPrint from '../views/app-dashboard/PSHE/views/Footprint/FootPrint';
import MyFootprint from '../views/app-dashboard/PSHE/views/my-footprint/MyFootprint';
import MySpace from '../views/app-dashboard/PSHE/views/my-space/MySpace';
import Safeguarding from '../views/app-dashboard/safeguarding/Safeguarding';
import SchoolActions from '../views/app-dashboard/school-actions/SchoolActions';
import ActionDetail from '../views/app-dashboard/school-actions/views/action-detail/ActionDetail';
import ActionPlanning from '../views/app-dashboard/school-actions/views/action-planning/ActionPlanning';
import ActionsReport from '../views/app-dashboard/school-actions/views/actions-report/ActionsReport';
import SchoolImpact from '../views/app-dashboard/school-impact/SchoolImpact';
import StaffOnboarding from '../views/app-dashboard/staff-onboarding/StaffOnboarding';
import Tracking from '../views/app-dashboard/tracking/Tracking';
import StudentTracking from '../views/app-dashboard/tracking/views/student-tracking/StudentTracking';
import TrackingReport from '../views/app-dashboard/tracking/views/tracking-report/TrackingReport';
import TrackingV2 from '../views/app-dashboard/tracking-v2/TrackingV2';
import Training from '../views/app-dashboard/training/Training';
import AssessmentTrial from '../views/app-dashboard/training/views/assessment-trial/AssessmentTrial';
import CompositeBiasExplained from '../views/app-dashboard/training/views/composite-bias-explained/CompositeBiasExplained';
import DisplayVideo from '../views/app-dashboard/training/views/display-video/DisplayVideo';
import FactorBiasExplained from '../views/app-dashboard/training/views/factor-bias-explained/FactorBiasExplanied';
import GroupRouter from './GroupRouter';
import StudentRouter from './StudentRouter';

const AppRouter = () => {
  const [{ session }] = useStore();

  const location = useLocation();
  const query = parseQuery<Record<string, string>>(location.search);
  const [{ user: sessionUser = {} }] = useSessionStorage<SessionType>('session', {});

  const {
    school_id: schoolId,
    school_code: schoolCode,
    staff_permission: permission,
    user: { level },
    school_details: { launch },
    packages,
  } = session;

  const isTrackingOnlySchool = useMemo(
    () => !(packages || []).includes('safeguarding'),
    [packages]
  );

  if (!level && !sessionUser?.level) {
    return <></>;
  }

  if (schoolCode === '999037' || schoolId === '37') {
    return <GroupRouter />;
  }

  if (level == 1) {
    return <StudentRouter />;
  }

  return (
    <Routes>
      {!session.hasCompletedOnboarding() && level != 3 ? (
        <Route path="/staff-onboarding" element={<StaffOnboarding />} />
      ) : (
        <Route path="/staff-onboarding" element={<Navigate replace to="/" />} />
      )}

      <Route path="/tracking-consent" element={<TrackingConsent />} />
      <Route path="/training/assessment-trial/" element={<AssessmentTrial />} />
      <Route path="/executive-record" element={<NewExecutive />} />
      <Route path="/new-year-setup/" element={<Navigate replace to="home" />} />
      <Route path="/new-year-setup/:page" element={<NewYearSetup />} />

      <Route path="/admin/new-year-setup/import-staffs" element={<ImportStaffsPage />} />
      <Route path="/admin/new-year-setup/import-students" element={<ImportStudentsPage />} />

      <Route path="/assessment-tracker/login-cards-print-view" element={<LoginCardsPrintView />} />

      <Route
        path="/*"
        element={
          <AppLayout
            isAuthenticated={Boolean(session?.user?.level)}
            customLevel={session?.user?.level || sessionUser?.level}
          >
            <Routes>
              {level === 3 ? (
                <Route path="/" element={<Navigate replace to="/admin/getting-started" />} />
              ) : permission == true && level == 4 ? (
                <Route path="/" element={<Navigate replace to="/training/" />} />
              ) : (
                <Route path="/" element={<Navigate replace to="/tracking/" />} />
              )}

              <Route path="/half-login" element={<HalfLogin />} />
              <Route path="/tracking" element={<TrackingV2 />} />
              <Route path="/tracking/student/:studentId" element={<StudentTracking />} />
              <Route path="/tracking-v1" element={<Tracking />} />
              <Route
                path="/assessment-tracker/"
                element={<Navigate replace to="/assessment-tracker/completed" />}
              />
              <Route path="/assessment-tracker/:page" element={<AssessmentTracker />} />

              <Route path="/safeguarding-v1" element={<Safeguarding />} />

              <Route path="/safeguarding/:page" element={<SafeguardingV2 />} />
              <Route path="/safeguarding" element={<Navigate replace to="/safeguarding/home" />} />

              <Route
                path="/school-actions/"
                element={<Navigate replace to="/school-actions/student-action-plans" />}
              />
              <Route path="/school-actions/:page" element={<SchoolActions />} />
              <Route path="/school-actions/:page/:actionId" element={<ActionDetail />} />
              <Route path="/school-actions/action-planning" element={<ActionPlanning />} />

              <Route path="/school-actions/action-plan-routes" element={<ActionPlanRoutes />} />
              <Route
                path="/school-actions/action-plan-routes/cascade-training-model"
                element={<CascadeTrainingModel mode={launch ? 'Launch' : 'Old'} />}
              />
              <Route
                path="/school-actions/action-plan-routes/peer-monitoring-training-model"
                element={<PeerMonitoringTrainingModel mode={launch ? 'Launch' : 'Old'} />}
              />
              <Route
                path="/school-actions/action-plan-routes/whole-school-training-model"
                element={<WholeSchoolTrainingModel mode={launch ? 'Launch' : 'Old'} />}
              />

              <Route path="/training" element={<TrainingV2 />} />
              <Route path="/training-v1" element={<Training />} />
              <Route path="/training/module/:module_id/:id" element={<DisplayVideo />} />
              <Route
                path="/training/factor-bias-explained/:biasType"
                element={<FactorBiasExplained />}
              />
              <Route
                path="/training/composite-bias-explained/:compositeType"
                element={<CompositeBiasExplained />}
              />
              <Route
                path="/training/self-regulation-explained/:selfRegulationType"
                element={<SelfRegulationExplained />}
              />

              <Route path="/pshe" element={<PSHE />} />
              <Route path="/pshe/footprint" element={<FootPrint />} />
              <Route path="/pshe/my-footprint" element={<MyFootprint />} />
              <Route path="/pshe/play-video" element={<QuickVideo />} />
              <Route path="/pshe/my-space" element={<MySpace />} />

              <Route
                path="/school-impact"
                element={<Navigate replace to="/school-impact/OVERVIEW" />}
              />
              <Route path="/school-impact/:page" element={<SchoolImpact />} />

              {level !== 4 && <Route path="/admin/*" element={<AdminDashboard />} />}

              <Route path="/reports" element={<Reports />} />
              <Route path="/reports/student-risk-report" element={<TrackingReport />} />

              {level !== 4 && (
                <Route
                  path="/reports/executive-summary-report"
                  element={
                    <ExecutiveSummary
                      schoolId={schoolId as string}
                      isTrackingOnlySchool={isTrackingOnlySchool}
                    />
                  }
                />
              )}
              <Route path="/reports/action-plan-report" element={<ActionsReport />} />
              {!(level == 4 || level == 6) && (
                <Route path="/reports/export-data" element={<ExportData />} />
              )}
            </Routes>
          </AppLayout>
        }
      />
      <Route path="/refresh" element={<Navigate to={query.url ?? '/'} />} />
    </Routes>
  );
};

export default AppRouter;
