//
//  FrittRegnskapAppDelegate.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright Knubo Borgen 2010. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <CoreData/NSManagedObjectContext.h>
#import <CoreData/NSManagedObjectModel.h>

@class FrittRegnskapViewController;

@interface FrittRegnskapAppDelegate : NSObject <UIApplicationDelegate> {
    UIWindow *window;
    FrittRegnskapViewController *viewController;
		
	NSManagedObjectModel *managedObjectModel;
    NSManagedObjectContext *managedObjectContext;
	NSPersistentStoreCoordinator *persistentStoreCoordinator;
	
}

@property (nonatomic, retain) IBOutlet UIWindow *window;
@property (nonatomic, retain) IBOutlet FrittRegnskapViewController *viewController;

@property (nonatomic, retain, readonly) NSManagedObjectModel *managedObjectModel;
@property (nonatomic, retain, readonly) NSManagedObjectContext *managedObjectContext;
@property (nonatomic, retain, readonly) NSPersistentStoreCoordinator *persistentStoreCoordinator;

- (NSString *)applicationDocumentsDirectory;
- (void) savePersons:(NSArray*) persons;
- (void) deleteAllPersons;
- (NSArray *) getPeople: (bool) sort;

@end

