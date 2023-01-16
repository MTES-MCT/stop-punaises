import user from '../../fixtures/user.json'

describe('Post front signalement with 4 as a result', { testIsolation: false }, () => {
  it ('Gets 4 as a result', () => {
    cy.visit('http://localhost:8090/')
    cy.get('#code-postal').type(user.codepostal)
    cy.get('.btn-next').click()
    cy.wait(1000)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_typeLogement_1').click()
    cy.get('#signalement_front_superficie').type(45)
    cy.get('.skip-search-address').click()
    cy.get('#signalement_front_adresse').type(user.address)
    cy.get('#signalement_front_codePostal').type(user.codepostal)
    cy.get('#signalement_front_ville').type(user.city)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_locataire_0').click()
    cy.get('#signalement_front_logementSocial_0').click()
    cy.get('#signalement_front_allocataire_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_dureeInfestation_0').click()
    cy.get('#signalement_front_infestationLogementsVoisins_0').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_piquresExistantes_0').click()
    cy.get('#signalement_front_piquresConfirmees_0').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_dejectionsTrouvees_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_oeufsEtLarvesTrouves_0').click()
    cy.get('#signalement_front_oeufsEtLarvesNombrePiecesConcernees_0').click()
    cy.get('#signalement_front_oeufsEtLarvesFaciliteDetections_0').click()
    cy.get('#signalement_front_oeufsEtLarvesLieuxObservations_0').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_punaisesTrouvees_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_nomOccupant').type(user.lastname)
    cy.get('#signalement_front_prenomOccupant').type(user.firstname)
    cy.get('#signalement_front_telephoneOccupant').type(user.telephone)
    cy.get('#signalement_front_emailOccupant').type(user.email)
    cy.get('section.current-step .if-territory-open .btn-next').click()
    cy.wait(300)
    cy.get('#niveau-infestation .niveau-infestation').contains('4')
  })

})
