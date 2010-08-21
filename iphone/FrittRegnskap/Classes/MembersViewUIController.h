//
//  MembersViewUIController.h
//
//  Created by Knut Erik Borgen on 20.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "FrittRegnskapAppDelegate.h"
#import "model/Person.h"
#import "model/Semester.h"
#import "model/SemesterMembership.h"
#import "model/YearMembership.h"

@interface MembersViewUIController : UITableViewController {
	IBOutlet FrittRegnskapAppDelegate *appDelegate;
	IBOutlet UINavigationItem *titleBar;
	IBOutlet UIBarButtonItem *statusBar;
	IBOutlet UIBarButtonItem *previousButton;
	IBOutlet UIBarButtonItem *nextButton;
	IBOutlet UIBarButtonItem *switchButton;
	int min_semester;
	int max_semester;
	int min_year;
	int max_year;
	int current_semester;
	int current_year;
	bool semesterView;
	
}


@property (nonatomic, retain) NSMutableArray *people;
@property (nonatomic, retain) NSMutableArray *indexes;
@property (nonatomic, retain) NSMutableArray *indexNames;
@property (nonatomic, retain) NSMutableDictionary *lookupMembership;

- (void) initView;
- (void) switchToSemesterView;
- (void) switchToYearView;
- (void) calculateIndexes;
- (IBAction)previousClicked:(id)sender;
- (IBAction)nextClicked:(id)sender;
- (IBAction)toggleYearSemester:(id)sender;


@end
