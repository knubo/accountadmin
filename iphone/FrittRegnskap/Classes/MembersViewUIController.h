//
//  MembersViewUIController.h
//
//  Created by Knut Erik Borgen on 20.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import "FrittRegnskapAppDelegate.h"
#import "model/Semester.h"
#import "model/SemesterMembership.h"

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


@property (nonatomic, retain) NSArray *people;
@property (nonatomic, retain) NSMutableArray *indexes;
@property (nonatomic, retain) NSMutableArray *indexNames;
@property (nonatomic, retain) NSMutableDictionary *lookupMembership;

- (void) initView;
- (void) switchToSemesterView;

@end
