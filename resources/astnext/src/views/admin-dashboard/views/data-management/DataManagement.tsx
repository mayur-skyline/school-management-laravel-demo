import React, { useEffect, useState } from 'react';
import { toast } from 'react-toastify';

import { TOAST_OPTIONS } from '../../../../common/configs';
import { type BookletResource } from '../../../../common/models/Admin';
import StaffDataEdit, { type IStaffData } from '../../../../common/models/StaffDataEdit';
import StudentData, { type IStudentData } from '../../../../common/models/StudentData';
import AmberButton from '../../../../components/base/button/AmberButton';
import HtmlTooltip from '../../../../components/base/tooltip/HtmlTooltip';
import Navbar from '../../../../components/nav-bar/Navbar';
import WarningToast from '../../../../components/toasts/WarningToast/WarningToast';
import { useShowError } from '../../../../context/modals/ModalProvider';
import { useGetAdminResourceData } from '../../../../hooks/data/useGetAdminResourceData';
import { useGettingStartedResource } from '../../../../hooks/data/useGettingStartedResource';
import { useUserConfigs } from '../../../../hooks/data/useUserConfigs';
import { useStore } from '../../../../hooks/useStore';
import ResourceModal from '../../../app-dashboard/training/components/resource-modal/ResourceModal';
import book from '../../assets/book.svg';
import editSettings from '../../assets/edit-setting.svg';
import importImage from '../../assets/import-image.svg';
import itDocumentImage from '../../assets/it-document.png';
import wonde from '../../assets/wonde.svg';
import PageHeader from '../../components/page-header/PageHeader';
import DataManagerModel from './components/model/DataManagerModel';
import ImportStaffCSV from './components/staff-csv/ImportStaffCSV';
import ImportStudentDataCSV from './components/student-CSV/ImportStudentDataCSV';
import StudentNameToggleTabs from './components/student-name-toggle-tabs/StudentNameToggleTabs';
import EditStaffData from './EditStaffData';
import EditStudentData from './EditStudentData';
import ImportStaffData from './ImportStaffData';
import ImportStudentData from './ImportStudentData';

const Resources: React.FC<{
  icon: any;
  title: string;
  className?: string;
  classContent?: string;
  loading?: boolean;

  onclick?: React.MouseEventHandler<HTMLDivElement>;
}> = ({ icon, title, onclick, className, classContent, loading }) => {
  return (
    <div className="mb-5">
      <div
        role="button"
        className={`inline-flex items-center text-gray-500 ${
          loading ? 'pointer-events-none' : 'pointer-events-auto'
        } ${className}`}
        onClick={onclick}
      >
        <div className={`${classContent}`}>
          <img src={icon} alt={icon} className={`mr-4 ${classContent}`} />
        </div>
        <p className="font-semibold">{title}</p>
      </div>
    </div>
  );
};

type DataManagement = React.HTMLAttributes<HTMLElement>;

const DataManagement: React.FC<DataManagement> = () => {
  const [modal, setModal] = useState<boolean>(false);
  const [title, setTitle] = useState<string>('');
  const [data, setData] = useState([]);
  const [showTable, setShowTable] = useState<boolean>(false);
  const [showStaffTable, setShowStaffTable] = useState(false);
  const [showModal, setShowModal] = useState<boolean>(false);
  const [studentData, setStudentData] = useState<IStudentData[]>([]);
  const [showStudentDataModal, setShowStudentDataModal] = useState(false);
  const [staffData, setStaffData] = useState<IStaffData[]>([]);
  const [showStaffCsvModal, setShowStaffCsvModal] = useState(false);
  const [showStaffImport, setShowStaffImport] = useState(false);

  const { pseudonymizing, updateConfigs } = useUserConfigs();
  const [
    {
      session: {
        school_id,

        package_subscription: packages,
        user: { level },
      },
    },
  ] = useStore();
  const { showError } = useShowError();
  const { loading, resource } = useGetAdminResourceData();
  const { loading: itLoading, resource: itResource } = useGettingStartedResource();

  function handleSetStudentNameMode(pseudonymizing: boolean) {
    updateConfigs({ pseudonymizing });
  }

  const handleDownload = (data: BookletResource) => {
    if (data.url.length > 0) {
      window.open(data.url);
    } else {
      toast(<WarningToast>No resource available</WarningToast>, TOAST_OPTIONS);
    }
  };

  const handleShow = () => {
    setShowModal(true);
  };

  const handleTable = () => {
    setShowTable(true);
  };

  const handleStaffTable = () => {
    setShowStaffTable(true);
  };

  const getStudentData = () => {
    StudentData.getEditDatatable(level)
      .then((pupils) => {
        setStudentData([...pupils]);
      })
      .catch((err) => showError(err));
  };

  const getStaffData = () => {
    const data = {
      school_id,
      my_level: level,
      filterData: [],
    };

    StaffDataEdit.getEditStaffDatatable(data).then((data) => setStaffData(data));
  };

  useEffect(() => {
    getStudentData();
    getStaffData();
  }, []);

  return (
    <div className="data-management p-12">
      <Navbar
        hideNavToggleButton
        leftArea={<PageHeader icon="data-management-active" title="Data Management" />}
        leftAreaStyle={{ width: '380px', padding: 0, margin: 0 }}
        rightArea={<></>}
        style={{ padding: 0, marginBottom: 42 }}
      />
      <ResourceModal
        open={modal}
        hideCloseButton={true}
        onClose={() => {
          setModal(false);
        }}
        title={title}
        data={data}
      />
      <div className="flex justify-between items-center bg-amber-alt rounded-3xl p-10 mb-16">
        <div
          role="button"
          className={`w-2/4 flex items-center ${
            itLoading ? 'pointer-events-none' : 'pointer-events-auto'
          }`}
          onClick={() => handleDownload(itResource.booklet[2])}
        >
          <img src={itDocumentImage} alt="itDocumentImage" width="60" className="mr-10" />
          <p className="text-xl font-bold">IT Lead Document</p>
        </div>
        <AmberButton
          style={{ boxShadow: '0 10px 15px -3px rgb(0 0 0 / 10%)' }}
          onClick={() => handleDownload(itResource.booklet[2])}
        >
          <p className="font-bold py-1 px-4">Download</p>
        </AmberButton>
      </div>
      <div className="w-full flex flex-wrap">
        <div className="w-1/2 mb-10">
          <p className="text-xl font-semibold mb-5">Student Data</p>

          {showStudentDataModal && (
            <DataManagerModel
              body={<ImportStudentDataCSV onClose={() => setShowStudentDataModal(false)} />}
              setShowModal={setShowStudentDataModal}
              title={null}
              title2={null}
            />
          )}

          {packages.includes('ast_admin_importpupildata') ? (
            <Resources
              icon={importImage}
              title="Import Student Data (CSV)"
              onclick={() => setShowStudentDataModal(true)}
              className="-ml-2"
            />
          ) : (
            <HtmlTooltip title="Your school doesn't currently use this module Contact STEER to upgrade">
              <span>
                <Resources
                  icon={importImage}
                  title="Import Student Data (CSV)"
                  className="-ml-2 cursor-not-allowed"
                />
              </span>
            </HtmlTooltip>
          )}

          {packages.includes('ast_admin_wondepupilimport') ? (
            <Resources
              icon={wonde}
              title="Import Student Data (Wonde)"
              onclick={() => handleShow()}
              classContent="backdrop-grayscale-0 bg-white/30"
            />
          ) : (
            <HtmlTooltip title="Your school doesn't currently use this module Contact STEER to upgrade">
              <span>
                <Resources
                  icon={wonde}
                  title="Import Student Data (Wonde)"
                  className="cursor-not-allowed"
                  classContent="backdrop-grayscale-0 bg-white/30"
                />
              </span>
            </HtmlTooltip>
          )}
          {showModal && (
            <DataManagerModel
              body={<ImportStudentData onClose={() => setShowModal(false)} />}
              setShowModal={setShowModal}
              title="Wonde"
              title2="Student Import"
            />
          )}
          {showTable && (
            <DataManagerModel
              setShowModal={setShowTable}
              body={<EditStudentData getStudentData={getStudentData} studentData={studentData} />}
              title={null}
              title2="STUDENT DATA"
            />
          )}
          {showStaffTable && (
            <DataManagerModel
              setShowModal={setShowStaffTable}
              body={<EditStaffData getStaffData={getStaffData} staffData={staffData} />}
              title={null}
              title2="TEACHER/STAFF DATA"
            />
          )}
          {packages.includes('ast_admin_editpupildata') && level !== 6 ? (
            <Resources
              icon={editSettings}
              title="Edit Student Data"
              onclick={() => handleTable()}
            />
          ) : (
            <HtmlTooltip
              title={
                level === 6
                  ? 'This is not available to consultants'
                  : "Your school doesn't currently use this module Contact STEER to upgrade"
              }
            >
              <span>
                <Resources
                  icon={editSettings}
                  title="Edit Student Data"
                  className="cursor-not-allowed"
                />
              </span>
            </HtmlTooltip>
          )}
        </div>
        <div className="w-1/2 mb-10">
          <p className="text-xl font-semibold mb-5">Staff Data</p>
          {packages.includes('ast_admin_importstaffdata') ? (
            <Resources
              icon={importImage}
              title="Import Staff Data (CSV)"
              onclick={() => setShowStaffCsvModal(true)}
              className="-ml-2"
            />
          ) : (
            <HtmlTooltip title="Your school doesn't currently use this module Contact STEER to upgrade">
              <span>
                <Resources
                  icon={importImage}
                  title="Import Staff Data (CSV)"
                  className="-ml-2 cursor-not-allowed"
                />
              </span>
            </HtmlTooltip>
          )}
          {showStaffCsvModal && (
            <DataManagerModel
              body={<ImportStaffCSV onClose={() => setShowStaffCsvModal(false)} />}
              setShowModal={setShowStaffCsvModal}
              title={null}
              title2={null}
            />
          )}
          {packages.includes('ast_admin_wondestaffimport') ? (
            <Resources
              icon={wonde}
              title="Import Staff Data (Wonde)"
              onclick={() => {
                setShowStaffImport(true);
              }}
              classContent="backdrop-grayscale-0 bg-white/30"
            />
          ) : (
            <HtmlTooltip title="Your school doesn't currently use this module Contact STEER to upgrade">
              <span>
                <Resources
                  icon={wonde}
                  title="Import Staff Data (Wonde)"
                  classContent="backdrop-grayscale-0 bg-white/30"
                  className="cursor-not-allowed"
                />
              </span>
            </HtmlTooltip>
          )}
          {showStaffImport && (
            <DataManagerModel
              setShowModal={setShowStaffImport}
              body={<ImportStaffData />}
              title="Wonde"
              title2="Staff Import"
            />
          )}
          {packages.includes('ast_admin_editstaffdata') ? (
            <Resources
              icon={editSettings}
              title="Edit Staff Data"
              onclick={() => handleStaffTable()}
            />
          ) : (
            <HtmlTooltip title="Your school doesn't currently use this module Contact STEER to upgrade">
              <span>
                <Resources
                  icon={editSettings}
                  title="Edit Staff Data"
                  className="cursor-not-allowed"
                />
              </span>
            </HtmlTooltip>
          )}
        </div>
        <div className="w-1/2 mb-10">
          <div className="flex mb-5">
            <img src={book} alt="book" width="25" className="mr-4" />
            <p className="text-xl font-semibold">Resources</p>
          </div>
          <Resources
            onclick={() => {
              setData(resource.technical);
              setTitle('Technical');
              setModal(true);
            }}
            loading={loading}
            icon={importImage}
            title="Technical"
            className="-ml-2"
          />
          <Resources
            onclick={() => {
              setData(resource.data_protection);
              setTitle('Data Protection');
              setModal(true);
            }}
            loading={loading}
            icon={editSettings}
            title="Data Protection"
          />
        </div>

        <div className="w-1/2 mb-10">
          <div className="flex mb-5">
            <p className="text-xl font-semibold">Privacy</p>
          </div>
          <p className="text-[15px] text-gray-500 mb-5">
            How do you want students&apos; name to appear on the platform?
          </p>
          <StudentNameToggleTabs
            mode={pseudonymizing}
            onModeChange={handleSetStudentNameMode}
            className="mx-2"
          />
        </div>
      </div>
    </div>
  );
};

export default DataManagement;
