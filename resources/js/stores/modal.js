import { reactive } from 'vue';

export const modalState = reactive({
  isAddUserOpen: false,
  isAddGroupOpen: false,
  isAddRoleOpen: false, 
  isNewFileModalOpen: false,
  isNewFolderModalOpen: false,
  isNewBatchModalOpen: false,
  isNewScannedModalOpen: false,
});
