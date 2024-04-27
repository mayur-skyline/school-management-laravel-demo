import { Table, TableBody, TableRow } from '@mui/material';
import React, { FC } from 'react';

import { checkOddOrEven } from '../../../../../common/helpers';
import { permState } from '../PermissionReducer';
import { TableCell } from '../Permissions';
import PermissionHeader from './PermissionHeader';
import PermissionRadioRow from './PermissionRadioRow';
import PermissionCheckboxRow from './PermissonCheckboxRow';
type PermissionTableType = {
  headerData: {
    year_arr: number[];
    campus_array: string[];
    house_array: string[];
  };
  headerName: string[];
  permissons: permState[];
  savingValid: boolean;
  onAllClick: (id: number) => void;
  onRadioClick: (rId: number, cId: number) => void;
  onAllCheckbox: (value: boolean, cId: number) => void;

  onCheckboxClick: (rId: number, cId: number, arrType: number) => void;
};
const PermissionTable: FC<PermissionTableType> = ({
  headerData,
  headerName,
  permissons,
  onAllClick,
  savingValid,
  onRadioClick,
  onAllCheckbox,
  onCheckboxClick,
}) => {
  return (
    <Table className="training-tracker-table " stickyHeader>
      {headerData.year_arr !== null && (
        <PermissionHeader
          items={headerName}
          year_arr={headerData.year_arr}
          campus_array={headerData.campus_array}
          house_array={headerData.house_array}
          onClick={onAllClick}
          onCclick={onAllCheckbox}
        />
      )}
      <TableBody>
        {permissons[0]?.row?.staff_id ? (
          permissons.map((tracker, key) => (
            <TableRow
              key={key}
              style={{
                background: checkOddOrEven(key) === 'ODD' ? '#FAFAFD' : 'white',
              }}
            >
              <TableCell
                style={{
                  position: 'sticky',
                  left: 225,
                  zIndex: 1,
                  background: checkOddOrEven(key) === 'ODD' ? '#FAFAFD' : 'white',
                }}
              >
                <h3 className="text-base font-bold">{tracker.row.staff_name}</h3>
              </TableCell>
              <PermissionRadioRow items={tracker.all} rId={key} onClick={onRadioClick} />
              {tracker.all[1] || tracker.all[2] ? (
                <TableCell className=" items-center ">
                  <p className="text-center">All years selected</p>
                </TableCell>
              ) : (
                <PermissionCheckboxRow
                  items={tracker.year}
                  title={tracker.row.staff_name}
                  onClick={onCheckboxClick}
                  arrType={0}
                  rId={key}
                  item_arr={[...headerData.year_arr]}
                  childComp={
                    tracker.errAt.year &&
                    savingValid && <p className="text-red-600 pt-2">Select at least one Year</p>
                  }
                />
              )}

              <PermissionCheckboxRow
                items={tracker.campus}
                title={tracker.row.staff_name}
                onClick={onCheckboxClick}
                item_arr={[...headerData.campus_array]}
                arrType={1}
                rId={key}
                childComp={
                  tracker.errAt.campus &&
                  savingValid && <p className="text-red-600 pt-2">Select at least one Campuses</p>
                }
              />
              {tracker.all[1] || tracker.all[3] ? (
                <TableCell className="items-center text-center">
                  <p className="text-center">All houses selected</p>
                </TableCell>
              ) : (
                <PermissionCheckboxRow
                  items={tracker.house}
                  title={tracker.row.staff_name}
                  onClick={onCheckboxClick}
                  arrType={2}
                  item_arr={[...headerData.house_array]}
                  rId={key}
                  childComp={
                    tracker.errAt.house &&
                    savingValid && <p className="text-red-600 pt-2">Select at least one House</p>
                  }
                />
              )}
            </TableRow>
          ))
        ) : (
          <TableRow>
            <TableCell colSpan={9}>
              <p className="text-center text-gray-500 my-20">Data not available</p>
            </TableCell>
          </TableRow>
        )}
      </TableBody>
    </Table>
  );
};
export default PermissionTable;
