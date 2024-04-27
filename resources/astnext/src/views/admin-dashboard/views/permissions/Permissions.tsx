import { styled, Table, TableBody, TableRow } from '@mui/material';
import Tab from '@mui/material/Tab';
import MaterialTableCell, { tableCellClasses } from '@mui/material/TableCell';
import Tabs from '@mui/material/Tabs';
import React, { FC, HTMLAttributes, useEffect, useReducer, useState } from 'react';

import PermissionModal, { IPermissionData } from '../../../../common/models/PermissionModal';
import Navbar from '../../../../components/nav-bar/Navbar';
import CircularProgress from '../../../../components/overrides/circular-progress/CircularProgress';
import { useShowError } from '../../../../context/modals/ModalProvider';
import { useGetPermissionModal } from '../../../../hooks/data/useGetPermissionTable';
import useSavePermissions from '../../../../hooks/data/useSavePermissions';
import { useStore } from '../../../../hooks/useStore';
import SelectDataScroll from '../../../app-dashboard/export-data/SelectDataScroll';
import PageHeader from '../../components/page-header/PageHeader';
import HighLevelTable from './permission-table/HighLevelTable';
import PermissionTable from './permission-table/PermissionTable';
import { ActionKind, AmberButtonPermission, atStartRow, reducer } from './PermissionReducer';

type Permissions = HTMLAttributes<HTMLElement>;

export const TableCell = styled(MaterialTableCell)(() => ({
  [`&.${tableCellClasses.head}`]: {
    borderRight: '1px solid rgba(224, 224, 224, 1)',
  },
  [`&.${tableCellClasses.body}`]: {
    borderRight: '1px solid rgba(224, 224, 224, 1)',
  },
}));

const Permissions: FC<Permissions> = () => {
  const [scollyear, setScrollYear] = useState<string>(null);
  const [errAt, setErrAt] = useState<boolean>(false);

  const [permissons, dispatch] = useReducer(reducer, atStartRow);
  const [{ session }] = useStore();
  const { level, id } = session.user;
  const [getLevel, setGetLevel] = useState(4);
  const { loading, resource } = useGetPermissionModal(getLevel, scollyear);
  const { saving, exporting, saveHandler, exportPermission } = useSavePermissions();
  const [barData, setBarData] = useState<IPermissionData>({ year_list: null, lead_sp_id: null });
  const { showError } = useShowError();

  useEffect(() => {
    if (!loading && resource.pupil_array) {
      getLevel === 4
        ? dispatch({
            type: ActionKind.Load,
            startData: {
              data: resource.pupil_array,
              yearArr: resource.year_arr,
              campus: resource.campus_array,
              house: resource.house_array,
            },
          })
        : dispatch({
            type: ActionKind.Level5,
            startData: {
              data: resource.pupil_array,

              campus: resource.campus_array,
            },
          });
    }
  }, [getLevel, resource, loading, dispatch]);

  useEffect(() => {
    PermissionModal.getYearData()
      .then((data) => {
        setBarData({ ...data });
        setScrollYear(data.acyear || null);
      })
      .catch((err) => showError(err));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const AllRowHandler = (id: number) => {
    getLevel === 4
      ? dispatch({
          type: ActionKind.AllClick,
          cId: id,
          startData: {
            campus: resource.campus_array,
            house: resource.house_array,
          },
        })
      : dispatch({ type: ActionKind.Allc5 });
  };

  const radioHandler = (rId: number, cId: number) => {
    getLevel === 4
      ? dispatch({
          type: ActionKind.Rclick,
          rId: rId,
          cId: cId,
          startData: {
            campus: resource.campus_array,
            house: resource.house_array,
          },
        })
      : dispatch({ type: ActionKind.Rcampus5, rId: rId, cId: cId });
  };

  const checkboxHandler = (rId: number, cId: number, arrType?: number) => {
    getLevel === 4
      ? dispatch({ type: ActionKind.Cclick, rId: rId, cId: cId, arrType: arrType })
      : dispatch({ type: ActionKind.Ccampus5, rId: rId, cId: cId });
  };

  const allCampuseHandler = (value: boolean, cId: number) => {
    if (getLevel === 4) {
      dispatch({ type: ActionKind.Allcampus, rId: value ? 0 : 1, cId: cId });
    }
  };

  const tableHandler = (e, n) => {
    if (n === 0) {
      dispatch({ type: ActionKind.Refrash });
      setGetLevel(4);
    } else {
      if (level >= 6 || (level === 5 && id === +barData.lead_sp_id)) {
        setGetLevel(5);
      }
    }
  };

  const saveManager = () => {
    let saveData: Array<{
      staffid: string;
      permissiontype: string;
      campus: string;
      year?: string;
      house?: string;
    }>;

    if (getLevel === 4) {
      let campusE = false,
        yearE = false,
        houseE = false;
      permissons.forEach((ele) => {
        campusE = ele.errAt.campus || campusE;
        yearE = ele.errAt.house || yearE;
        houseE = ele.errAt.year || houseE;
      });
      if (campusE || yearE || houseE) {
        setErrAt(true);
        return;
      }

      saveData = permissons.map((ele) => {
        let pType = '',
          campus = '',
          year = '',
          house = '';
        ele.all.forEach((v, id) => {
          if (v) {
            pType =
              id === 0
                ? 'training'
                : id === 1
                ? 'all'
                : id === 2
                ? 'year'
                : id === 3
                ? 'hs'
                : 'custom';
          }
        });

        campus = resource.campus_array.filter((v, i) => ele.campus[i]).join(',');
        year = resource.year_arr
          .filter((v, i) => {
            if (ele.year[i]) return v.toString();
          })
          .join(',');
        house = resource.house_array.filter((v, i) => ele.house[i]).join(',');
        return {
          staffid: ele.row.staff_id.toString(),
          permissiontype: pType,
          campus: campus,
          year: year,
          house: house,
        };
      });
    } else {
      saveData = permissons.map((ele) => {
        let pType = '',
          campus = '';

        pType = ele.allCampus ? 'cs' : '';
        campus = resource.campus_array.filter((v, i) => ele.campus[i]).join(',');

        return {
          staffid: ele.row.staff_id.toString(),
          permissiontype: pType,
          campus: campus,
        };
      });
    }

    setErrAt(false);
    saveHandler({
      year: scollyear,
      level: getLevel.toString(),
      staffarray: saveData,
    });
  };

  const exportManager = () => {
    exportPermission({
      level: getLevel.toString(),
      year: scollyear,
    });
  };

  return (
    <div className="permissions p-12">
      <Navbar
        hideNavToggleButton
        leftArea={<PageHeader icon="permissions-active" title="Permissions" />}
        leftAreaStyle={{ width: '380px', padding: 0, margin: 0 }}
        rightArea={<></>}
        style={{ padding: 0, marginBottom: 42 }}
      />
      <div className="m-auto p-auto">
        <div className="flex mb-8">
          {barData.year_list && (
            <SelectDataScroll
              key="years_permData"
              items={barData.year_list}
              onScroll={(key) => setScrollYear(key)}
              firstYear={barData.acyear}
            />
          )}
          <div className=" mt-2 mr-auto">
            {saving ? (
              <div className="pr-10 pl-8 inline-flex items-center justify-center">
                <CircularProgress />
              </div>
            ) : (
              <AmberButtonPermission title="Save" onClick={saveManager} />
            )}
            {exporting ? (
              <div className="pr-10 pl-8 inline-flex items-center justify-center">
                <CircularProgress />
              </div>
            ) : (
              <AmberButtonPermission title="Export" onClick={exportManager} />
            )}
          </div>
        </div>
      </div>
      <Tabs
        value={getLevel - 4}
        onChange={tableHandler}
        TabIndicatorProps={{ style: { backgroundColor: '#ffd44b' } }}
      >
        <Tab
          label="Level 4 Permissions"
          sx={{
            color: '#e5e7eb',
            fontWeight: '700',
            '&.Mui-selected': {
              color: 'rgba(0, 0, 0, 0.87)',
            },
          }}
        />
        {(level >= 6 || (level === 5 && id === +barData.lead_sp_id)) && (
          <Tab
            label="Level 5 Permissions"
            sx={{
              color: '#e5e7eb',
              fontWeight: '700',
              '&.Mui-selected': {
                color: 'rgba(0, 0, 0, 0.87)',
              },
            }}
          />
        )}
      </Tabs>
      <div className="border-b border-solid"></div>

      {loading && permissons.length > 1 ? (
        <Table className="training-tracker-table">
          <TableBody>
            <TableRow>
              <TableCell colSpan={9}>
                <div className="flex items-center justify-center my-20">
                  <CircularProgress />
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      ) : getLevel === 5 ? (
        <HighLevelTable
          headerData={{ campus_array: resource.campus_array }}
          headerName={['All Campuses', 'Campuses']}
          permissons={permissons}
          onAllClick={AllRowHandler}
          onRadioClick={radioHandler}
          onCheckboxClick={checkboxHandler}
        />
      ) : (
        <PermissionTable
          headerData={{
            campus_array: resource.campus_array,
            year_arr: resource.year_arr,
            house_array: resource.house_array,
          }}
          headerName={[
            'Training',
            'All Years & Houses',
            'All Years',
            'All Houses',
            'Custom',
            'Years',
            'Campuses',
            'Houses',
          ]}
          savingValid={errAt}
          permissons={permissons}
          onAllClick={AllRowHandler}
          onRadioClick={radioHandler}
          onCheckboxClick={checkboxHandler}
          onAllCheckbox={allCampuseHandler}
        />
      )}
    </div>
  );
};

export default Permissions;
