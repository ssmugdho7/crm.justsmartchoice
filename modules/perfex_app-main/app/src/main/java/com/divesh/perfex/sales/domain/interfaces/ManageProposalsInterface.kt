package com.divesh.perfex.sales.domain.interfaces

import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel

interface ManageProposalsInterface {
    fun onProposalClicked(proposal: ProposalsResponseModel.Proposal)
}