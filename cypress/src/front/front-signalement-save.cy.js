describe('Save the signalement', () => {
  it ('Saves the signalement', () => {
    cy.get('.if-recommandation-not-zero .btn-next').click()
    cy.wait(300)
    cy.get('#step-professionnel_info .btn-next-next').click()
    cy.wait(300)
  })

})
